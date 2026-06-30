<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Station;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class HostController extends Controller
{
    public function index()
    {
        $user    = auth()->user();
        $station = Station::where('user_id', $user->id)->first();

        if (!$station) {
            return Inertia::render('Host/Dashboard', [
                'station'        => null,
                'stats'          => ['total_revenue' => 0, 'month_revenue' => 0, 'growth' => 0],
                'recent_guests'  => [],
                'weekly_chart'   => [],
                'all_bookings'   => [],
            ]);
        }

        $stationId     = $station->id;
        $validStatuses = ['Booked', 'Selesai', 'Lunas'];

        $totalRevenue = Booking::where('station_id', $stationId)
            ->whereIn('status', $validStatuses)
            ->sum('total_price');

        $currentMonthRevenue = Booking::where('station_id', $stationId)
            ->whereIn('status', $validStatuses)
            ->whereMonth('booking_date', Carbon::now()->month)
            ->whereYear('booking_date', Carbon::now()->year)
            ->sum('total_price');

        $lastMonthRevenue = Booking::where('station_id', $stationId)
            ->whereIn('status', $validStatuses)
            ->whereMonth('booking_date', Carbon::now()->subMonth()->month)
            ->whereYear('booking_date', Carbon::now()->subMonth()->year)
            ->sum('total_price');

        $growth = $lastMonthRevenue > 0
            ? round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100)
            : ($currentMonthRevenue > 0 ? 100 : 0);

        $recentGuests = Booking::with('user')
            ->where('station_id', $stationId)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($b) => [
                'user_id'      => $b->user_id,
                'guest_name'   => $b->user->name ?? $b->user->username ?? 'Pengguna',
                'car'          => trim(($b->user->car_brand ?? '') . ' ' . ($b->user->car_series ?? '')) ?: 'Kendaraan Listrik',
                'plat'         => $b->plate_number ?? $b->user->nomor_plat ?? '-',
                'booking_code' => $b->booking_code,
                'amount'       => $b->total_price,
                'status'       => $b->status,
                'booking_slot' => $b->booking_slot ?? null,
                'date'         => Carbon::parse($b->booking_date)->diffForHumans(),
            ]);

        // ── Semua booking untuk tabel manajemen (host bisa hapus) ──────────
        $allBookings = Booking::with('user')
            ->where('station_id', $stationId)
            ->orderBy('booking_date', 'desc')
            ->get()
            ->map(fn($b) => [
                'id'           => $b->id,
                'booking_code' => $b->booking_code,
                'guest_name'   => $b->user->name ?? $b->user->username ?? 'Pengguna',
                'plat'         => $b->plate_number ?? $b->user->nomor_plat ?? '-',
                'booking_slot' => $b->booking_slot ?? '-',
                'booking_date' => Carbon::parse($b->booking_date)->format('d M Y'),
                'duration'     => $b->duration . ' mnt',
                'port_type'    => $b->port_type ?? '-',
                'total_price'  => $b->total_price,
                'status'       => $b->status,
            ]);

        return Inertia::render('Host/Dashboard', [
            'station'       => $station,
            'stats'         => [
                'total_revenue' => $totalRevenue,
                'month_revenue' => $currentMonthRevenue,
                'growth'        => $growth,
            ],
            'recent_guests' => $recentGuests,
            'weekly_chart'  => $this->buildWeeklyChart($stationId, $validStatuses),
            'all_bookings'  => $allBookings,
        ]);
    }
      public function storeStation(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'address'       => 'required|string',
            'city'          => 'required|string|max:255',
            'phone'         => 'nullable|string|max:20',
            'type'          => 'nullable|string|max:100',
            'location_type' => 'nullable|string|max:100',
            'price'         => 'nullable|numeric|min:0',
            'service_fee'   => 'nullable|numeric|min:0',
            'lat'           => 'nullable|numeric|between:-90,90',
            'lng'           => 'nullable|numeric|between:-180,180',
        ]);

        Station::create([
            'user_id'       => auth()->id(),
            'name'          => $request->name,
            'address'       => $request->address,
            'city'          => $request->city,
            'phone'         => $request->phone,
            'type'          => $request->type,
            'location_type' => $request->location_type,
            'price'         => $request->price       ?? 0,
            'service_fee'   => $request->service_fee ?? 0,
            'lat'           => $request->lat         ?: null,
            'lng'           => $request->lng         ?: null,
            'status'        => 'Tutup',
            'is_open'       => false,
        ]);

        return back();
    }
    /**
     * GET /host/chart-data  — polling realtime dari frontend
     */
    public function chartData()
    {
        $user    = auth()->user();
        $station = Station::where('user_id', $user->id)->first();

        if (!$station) {
            return response()->json(['weekly_chart' => [], 'stats' => []]);
        }

        $stationId     = $station->id;
        $validStatuses = ['Booked', 'Selesai', 'Lunas'];

        $totalRevenue = Booking::where('station_id', $stationId)
            ->whereIn('status', $validStatuses)->sum('total_price');

        $monthRevenue = Booking::where('station_id', $stationId)
            ->whereIn('status', $validStatuses)
            ->whereMonth('booking_date', Carbon::now()->month)
            ->whereYear('booking_date', Carbon::now()->year)
            ->sum('total_price');

        $todayBookings = Booking::where('station_id', $stationId)
            ->whereIn('status', $validStatuses)
            ->whereDate('booking_date', Carbon::today())
            ->count();

        // Semua booking (untuk refresh tabel setelah hapus)
        $allBookings = Booking::with('user')
            ->where('station_id', $stationId)
            ->orderBy('booking_date', 'desc')
            ->get()
            ->map(fn($b) => [
                'id'           => $b->id,
                'booking_code' => $b->booking_code,
                'guest_name'   => $b->user->name ?? $b->user->username ?? 'Pengguna',
                'plat'         => $b->plate_number ?? $b->user->nomor_plat ?? '-',
                'booking_slot' => $b->booking_slot ?? '-',
                'booking_date' => Carbon::parse($b->booking_date)->format('d M Y'),
                'duration'     => $b->duration . ' mnt',
                'port_type'    => $b->port_type ?? '-',
                'total_price'  => $b->total_price,
                'status'       => $b->status,
            ]);

        return response()->json([
            'weekly_chart'   => $this->buildWeeklyChart($stationId, $validStatuses),
            'total_revenue'  => $totalRevenue,
            'month_revenue'  => $monthRevenue,
            'today_bookings' => $todayBookings,
            'all_bookings'   => $allBookings,
            'timestamp'      => now()->toISOString(),
        ]);
    }

    /**
     * Helper: bangun data grafik mingguan
     */
    private function buildWeeklyChart(int $stationId, array $validStatuses): array
    {
        $weeklyChart = [];
        $maxRevenue  = 0;

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);

            $dailySum = Booking::where('station_id', $stationId)
                ->whereIn('status', $validStatuses)
                ->whereDate('booking_date', $date->format('Y-m-d'))
                ->sum('total_price');

            $dailyCount = Booking::where('station_id', $stationId)
                ->whereIn('status', $validStatuses)
                ->whereDate('booking_date', $date->format('Y-m-d'))
                ->count();

            if ($dailySum > $maxRevenue) $maxRevenue = $dailySum;

            $weeklyChart[] = [
                'day'       => $date->locale('id')->isoFormat('ddd'),
                'date'      => $date->format('d M'),
                'raw_value' => (int) $dailySum,
                'count'     => (int) $dailyCount,
                'is_today'  => $i === 0,
            ];
        }

        return array_map(function ($item) use ($maxRevenue) {
            $pct = $maxRevenue > 0 ? ($item['raw_value'] / $maxRevenue) * 100 : 0;
            return array_merge($item, ['value' => round($pct, 1)]);
        }, $weeklyChart);
    }

    public function toggleStatus(Request $request)
    {
        $request->validate(['is_open' => 'required|boolean']);

        $station = Station::where('user_id', auth()->id())->first();
        if ($station) {
            $station->update([
                'is_open' => $request->is_open,
                'status'  => $request->is_open ? 'Tersedia' : 'Tutup',
            ]);
        }

        return back();
    }

    public function showGuest($id)
    {
        $user    = auth()->user();
        $station = Station::where('user_id', $user->id)->first();

        if (!$station) return redirect()->route('host.dashboard');

        $guest         = User::findOrFail($id);
        $bookingsQuery = Booking::where('station_id', $station->id)->where('user_id', $id);

        $totalSpent    = (clone $bookingsQuery)->whereIn('status', ['Booked', 'Selesai', 'Lunas'])->sum('total_price');
        $totalVisits   = (clone $bookingsQuery)->count();
        $lastVisitDate = (clone $bookingsQuery)->latest('booking_date')->value('booking_date');

        $history = $bookingsQuery->orderBy('booking_date', 'desc')->get()->map(fn($b) => [
            'id'           => $b->id,
            'booking_code' => $b->booking_code,
            'booking_slot' => $b->booking_slot ?? null,
            'date'         => Carbon::parse($b->booking_date)->format('d M Y'),
            'amount'       => $b->total_price,
            'status'       => $b->status,
            'duration'     => $b->duration . ' Menit',
            'port_type'    => $b->port_type ?? '-',
            'plate_number' => $b->plate_number ?? $guest->nomor_plat ?? '-',
        ]);

        return Inertia::render('Host/GuestDetail', [
            'guest'   => [
                'id'            => $guest->id,
                'name'          => $guest->name ?? $guest->username ?? 'Tamu',
                'email'         => $guest->email,
                'nomor_plat'    => $guest->nomor_plat    ?? '-',
                'car_brand'     => $guest->car_brand     ?? '-',
                'car_series'    => $guest->car_series    ?? '-',
                'car_type'      => $guest->car_type      ?? '-',
                'nomor_telepon' => $guest->nomor_telepon ?? '-',
            ],
            'history' => $history,
            'stats'   => [
                'total_spent'  => $totalSpent,
                'total_visits' => $totalVisits,
                'last_visit'   => $lastVisitDate
                    ? Carbon::parse($lastVisitDate)->locale('id')->diffForHumans()
                    : 'Belum pernah',
            ],
        ]);
    }
}