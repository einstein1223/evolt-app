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
        // --- 1. PRE-CALCULATE NAIVE BAYES STATS ---
        $stats = Cache::remember('nb_stats', 60, function () {
            $totalDocs = TrainingData::count();
            if ($totalDocs == 0) return null;

            $totalT = TrainingData::where('kepadatan', 'T')->count();
            $totalP = TrainingData::where('kepadatan', 'P')->count();

            return [
                'totalDocs' => $totalDocs,
                'probT' => $totalT / $totalDocs,
                'probP' => $totalP / $totalDocs,
                'totalT' => $totalT,
                'totalP' => $totalP,
                'data' => TrainingData::all() 
            ];
        });

        // --- 2. TENTUKAN KONTEKS WAKTU ---
        $hour = Carbon::now()->hour;
        if ($hour >= 6 && $hour < 11) $currentWaktu = 'Pagi';
        elseif ($hour >= 11 && $hour < 15) $currentWaktu = 'Siang';
        else $currentWaktu = 'Sore';
        
        $currentHari = Carbon::now()->isWeekday() ? 'WD' : 'WE';

        // --- 3. AMBIL DATA STATION ---
        // (Opsional) Jika ingin MENYEMBUNYIKAN station tutup dari peta, tambahkan ->where('is_open', true)
        $stationsRaw = Station::with(['bookings' => function($query) {
            $query->where('status', '!=', 'Batal')
                  ->whereDate('booking_date', '>=', Carbon::today());
        }, 'user'])->get();

        // --- 4. MAPPING DATA ---
        $mappedStations = $stationsRaw->map(function ($station) use ($stats, $currentWaktu, $currentHari) {
            
            // Ekstrak Charger
            $chargersDetail = $station->chargers_detail ?? [];
            $chargerTypes = collect($chargersDetail)->pluck('type')->toArray();
            if (empty($chargerTypes)) $chargerTypes = ['Type 2'];
            $power = collect($chargersDetail)->first()['power'] ?? '22 kW';

            // Fitur Stasiun
            $hasDC = collect($chargerTypes)->contains(fn($t) => str_contains(strtoupper($t), 'CCS') || str_contains(strtoupper($t), 'DC'));
            $currentKonektor = $hasDC ? 'DC' : 'AC';
            $currentLokasi = $station->location_type ?? 'Station Umum';

            // --- JALANKAN KLASIFIKASI (Hanya jika BUKA) ---
            $nbPrediction = 'TUTUP';
            $isRecommended = false;
            $isBookable = false;

            // --- CEK STATUS BUKA/TUTUP DARI DB ---
            // Pastikan kolom 'is_open' sudah ada di DB dan Model (cast boolean)
            if ($station->is_open) {
                $nbPrediction = $this->calculateProbability($stats, $currentWaktu, $currentHari, $currentKonektor, $currentLokasi);
                $isRecommended = ($nbPrediction === 'Sangat Direkomendasikan (Sepi)');
                $isBookable = true;
            } else {
                $nbPrediction = 'STATION TUTUP';
            }

            return [
                'id' => $station->id,
                'name' => $station->name,
                'owner_name' => $station->user->name ?? 'Host E-VOLT',
                'location' => $station->address . ', ' . $station->city,
                'lat' => (float) $station->lat,
                'lng' => (float) $station->lng,
                'serviceFee' => $station->service_fee,
                'price' => $station->price,
                'is_private' => (bool) $station->is_private,
                
                // Status Penting untuk Frontend
                'is_open' => (bool) $station->is_open, // Kirim status asli ke Vue
                'recommendation_status' => $nbPrediction, // Hasil Prediksi atau "TUTUP"
                'is_recommended' => $isRecommended,
                'isBookable' => $isBookable, // Kunci tombol booking

                'chargers' => $chargerTypes,
                'power' => $power,
                'todayBookings' => $station->bookings->map(function($b) {
                    return [
                        'start_time' => Carbon::parse($b->booking_date)->format('H:i'),
                        'duration_minutes' => (int) $b->duration,
                        'port_type' => $b->port_type
                    ];
                }),
            ];
        });

        return Inertia::render('user/MapResult', [ 
            'dbStations' => $mappedStations,
            'dbBrands' => ['Hyundai', 'Wuling', 'Toyota', 'BMW'], 
            'dbTypes' => ['Type 2', 'CCS2', 'CHAdeMO'],
            'dbLocations' => Station::select('city')->distinct()->pluck('city'),
            'filters' => $request->all()
        ]);
    }

    /**
     * Algoritma Naive Bayes
     */
    private function calculateProbability($stats, $waktu, $hari, $konektor, $lokasi)
    {
        if (!$stats) return 'Data Belum Cukup';

        $data = $stats['data'];
        
        $calcLikelihood = function($field, $val, $classLabel) use ($data, $stats) {
            $count = $data->where($field, $val)->where('kepadatan', $classLabel)->count();
            $totalClass = ($classLabel == 'T') ? $stats['totalT'] : $stats['totalP'];
            return $totalClass > 0 ? ($count / $totalClass) : 0;
        };

        $p_Waktu_T = $calcLikelihood('waktu', $waktu, 'T');
        $p_Waktu_P = $calcLikelihood('waktu', $waktu, 'P');
        $p_Hari_T = $calcLikelihood('hari', $hari, 'T');
        $p_Hari_P = $calcLikelihood('hari', $hari, 'P');
        $p_Konektor_T = $calcLikelihood('konektor', $konektor, 'T');
        $p_Konektor_P = $calcLikelihood('konektor', $konektor, 'P');
        $p_Lokasi_T = $calcLikelihood('lokasi', $lokasi, 'T');
        $p_Lokasi_P = $calcLikelihood('lokasi', $lokasi, 'P');

        $scoreT = $stats['probT'] * ($p_Waktu_T ?: 0.01) * ($p_Hari_T ?: 0.01) * ($p_Konektor_T ?: 0.01) * ($p_Lokasi_T ?: 0.01);
        $scoreP = $stats['probP'] * ($p_Waktu_P ?: 0.01) * ($p_Hari_P ?: 0.01) * ($p_Konektor_P ?: 0.01) * ($p_Lokasi_P ?: 0.01);

        return ($scoreT >= $scoreP) ? 'Sangat Direkomendasikan (Sepi)' : 'Kemungkinan Padat';
    }
}