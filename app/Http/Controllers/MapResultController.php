<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Station;
use App\Models\TrainingData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class MapResultController extends Controller
{
    public function index(Request $request)
    {
        // ── 1. PRE-CALCULATE NAIVE BAYES STATS ──────────────────────────────
        $stats = Cache::remember('nb_stats', 60, function () {
            $totalDocs = TrainingData::count();
            if ($totalDocs === 0) return null;

            $totalT = TrainingData::where('kepadatan', 'T')->count();
            $totalP = TrainingData::where('kepadatan', 'P')->count();

            return [
                'totalDocs' => $totalDocs,
                'probT'     => $totalT / $totalDocs,
                'probP'     => $totalP / $totalDocs,
                'totalT'    => $totalT,
                'totalP'    => $totalP,
                'data'      => TrainingData::all(),
            ];
        });

        // ── 2. KONTEKS WAKTU SEKARANG ────────────────────────────────────────
        $hour = Carbon::now('Asia/Jakarta')->hour;
        if ($hour >= 6 && $hour < 11)       $currentWaktu = 'Pagi';
        elseif ($hour >= 11 && $hour < 15)  $currentWaktu = 'Siang';
        else                                 $currentWaktu = 'Sore';

        $currentHari = Carbon::now('Asia/Jakarta')->isWeekday() ? 'WD' : 'WE';

        // ── 3. AMBIL DATA STASIUN ────────────────────────────────────────────
        $stationsRaw = Station::with([
            'bookings' => function ($query) {
                $query->where('status', '!=', 'Batal')
                      ->whereDate('booking_date', '>=', Carbon::today('Asia/Jakarta'));
            },
            'user',
        ])->get();

        // ── 4. MAPPING DATA ──────────────────────────────────────────────────
        $mappedStations = $stationsRaw->map(function ($station) use ($stats, $currentWaktu, $currentHari) {

            // Parsing chargers_detail (bisa JSON string atau array)
            $chargersDetail = $station->chargers_detail ?? [];
            if (is_string($chargersDetail)) {
                $chargersDetail = json_decode($chargersDetail, true) ?? [];
            }

            // ── Ekstrak tipe & daya dari chargers_detail ──────────────────
            // Support dua format key: {tipe/daya} atau {type/power}
            $chargerTypes = collect($chargersDetail)
                ->map(fn($c) => $c['tipe'] ?? $c['type'] ?? null)
                ->filter()
                ->values()
                ->toArray();

            if (empty($chargerTypes)) $chargerTypes = ['Type 2'];

            $firstCharger = collect($chargersDetail)->first();
            $power        = $firstCharger['daya'] ?? $firstCharger['power'] ?? '22 kW';

            // Tentukan konektor AC / DC
            $hasDC           = collect($chargerTypes)->contains(
                fn($t) => str_contains(strtoupper($t), 'CCS') || str_contains(strtoupper($t), 'DC')
            );
            $currentKonektor = $hasDC ? 'DC' : 'AC';
            $currentLokasi   = $station->location_type ?? 'Station Umum';

            // ── Klasifikasi Naive Bayes (hanya jika stasiun buka) ─────────
            $nbPrediction  = 'TUTUP';
            $isRecommended = false;
            $isBookable    = false;

            if ($station->is_open) {
                $nbPrediction  = $this->calculateProbability($stats, $currentWaktu, $currentHari, $currentKonektor, $currentLokasi);
                $isRecommended = ($nbPrediction === 'Sangat Direkomendasikan (Sepi)');
                $isBookable    = true;
            } else {
                $nbPrediction = 'STATION TUTUP';
            }

            // ── Booking hari ini ──────────────────────────────────────────
            $todayBookings = $station->bookings->map(function ($b) {
                return [
                    'start_time'       => Carbon::parse($b->booking_date)->format('H:i'),
                    'duration_minutes' => (int) $b->duration,
                    'port_type'        => $b->port_type,
                ];
            });

            return [
                'id'                    => $station->id,
                'name'                  => $station->name,
                'owner_name'            => optional($station->user)->name ?? 'Host E-VOLT',
                'location'              => trim(($station->address ?? '') . ', ' . ($station->city ?? ''), ', '),
                'lat'                   => (float) $station->lat,
                'lng'                   => (float) $station->lng,
                'service_fee'           => $station->service_fee,
                'serviceFee'            => $station->service_fee,  // alias untuk kompatibilitas Vue
                'price'                 => $station->price,
                'is_private'            => (bool) $station->is_private,

                // Status & rekomendasi
                'is_open'               => (bool) $station->is_open,
                'status'                => $station->is_open ? 'Tersedia' : 'Tutup',
                'recommendation_status' => $nbPrediction,
                'is_recommended'        => $isRecommended,
                'isBookable'            => $isBookable,

                // Charger info — dikirim dua format agar Vue helper safeParseArray tetap bekerja
                'chargers_detail'       => $chargersDetail,
                'chargers'              => $chargerTypes,
                'type'                  => $chargerTypes[0] ?? 'Type 2',   // shortcut untuk getStationType()
                'power'                 => $power,                          // shortcut untuk getStationPower()
                'safeType'              => $chargerTypes[0] ?? 'Type 2',
                'safePower'             => $power,

                'todayBookings'         => $todayBookings,
            ];
        });

        return Inertia::render('user/MapResult', [
            'dbStations'  => $mappedStations,
            'dbBrands'    => ['Hyundai', 'Wuling', 'Toyota', 'BMW'],
            'dbTypes'     => ['Type 2', 'CCS2', 'CHAdeMO'],
            'dbLocations' => Station::select('city')->distinct()->pluck('city'),
            'filters'     => $request->all(),
        ]);
    }

    /**
     * Algoritma Naive Bayes — Klasifikasi kepadatan stasiun.
     * Return: 'Sangat Direkomendasikan (Sepi)' | 'Kemungkinan Padat' | 'Data Belum Cukup'
     */
    private function calculateProbability($stats, string $waktu, string $hari, string $konektor, string $lokasi): string
    {
        if (!$stats) return 'Data Belum Cukup';

        $data = $stats['data'];

        // Hitung likelihood P(fitur | kelas) dengan Laplace smoothing sederhana (pakai 0.01 jika 0)
        $likelihood = function (string $field, string $val, string $classLabel) use ($data, $stats): float {
            $count      = $data->where($field, $val)->where('kepadatan', $classLabel)->count();
            $totalClass = ($classLabel === 'T') ? $stats['totalT'] : $stats['totalP'];
            return $totalClass > 0 ? ($count / $totalClass) : 0.0;
        };

        $scoreT = $stats['probT']
            * ($likelihood('waktu',    $waktu,    'T') ?: 0.01)
            * ($likelihood('hari',     $hari,     'T') ?: 0.01)
            * ($likelihood('konektor', $konektor, 'T') ?: 0.01)
            * ($likelihood('lokasi',   $lokasi,   'T') ?: 0.01);

        $scoreP = $stats['probP']
            * ($likelihood('waktu',    $waktu,    'P') ?: 0.01)
            * ($likelihood('hari',     $hari,     'P') ?: 0.01)
            * ($likelihood('konektor', $konektor, 'P') ?: 0.01)
            * ($likelihood('lokasi',   $lokasi,   'P') ?: 0.01);

        return ($scoreT >= $scoreP)
            ? 'Sangat Direkomendasikan (Sepi)'
            : 'Kemungkinan Padat';
    }
}