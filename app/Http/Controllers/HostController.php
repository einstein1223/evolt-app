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
        $user = auth()->user();
        
        // 1. Ambil Station milik Host
        $station = Station::where('user_id', $user->id)->first();

        if (!$station) {
            return Inertia::render('Host/Dashboard', [
                'station' => null,
                'stats' => ['total_revenue' => 0, 'month_revenue' => 0, 'growth' => 0],
                'recent_guests' => [],
                'weekly_chart' => []
            ]);
        }

        // --- PERBAIKAN 1: Gunakan station_id (Lebih Akurat daripada Nama) ---
        $stationId = $station->id;

        // --- PERBAIKAN 2: Masukkan status 'Booked' agar terhitung ---
        // Kita hitung status 'Booked' (sudah bayar tapi belum cas) dan 'Selesai'
        $validStatuses = ['Booked', 'Selesai']; 

        // 2. Calculate Total Revenue
        $totalRevenue = Booking::where('station_id', $stationId)
            ->whereIn('status', $validStatuses) // Pakai whereIn untuk banyak status
            ->sum('total_price');

        // 3. Calculate Monthly Revenue
        $currentMonthRevenue = Booking::where('station_id', $stationId)
            ->whereIn('status', $validStatuses)
            ->whereMonth('booking_date', Carbon::now()->month)
            ->whereYear('booking_date', Carbon::now()->year)
            ->sum('total_price');

        // 4. Calculate Growth
        $lastMonthRevenue = Booking::where('station_id', $stationId)
            ->whereIn('status', $validStatuses)
            ->whereMonth('booking_date', Carbon::now()->subMonth()->month)
            ->whereYear('booking_date', Carbon::now()->subMonth()->year)
            ->sum('total_price');

        if ($lastMonthRevenue > 0) {
            $growth = round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100);
        } else {
            $growth = $currentMonthRevenue > 0 ? 100 : 0;
        }

        // 5. Recent Guests Data (Daftar Tamu Terbaru)
        // Disini kita TIDAK filter status, agar status 'Batal' pun terlihat di list (opsional)
        // atau gunakan whereIn jika ingin menyembunyikan yg batal.
        $recentGuests = Booking::with('user')
            ->where('station_id', $stationId) // Ubah ke ID
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($booking) {
                return [
                    'user_id' => $booking->user_id,
                    'guest_name' => $booking->user->name ?? 'Pengguna',
                    'car' => 'Kendaraan Listrik',
                    'plat' => $booking->booking_code ?? '-', 
                    'amount' => $booking->total_price,
                    // Status perlu ditampilkan di frontend agar host tau ini baru Booked
                    'status' => $booking->status, 
                    'date' => Carbon::parse($booking->booking_date)->diffForHumans(),
                ];
            });

        // 6. Weekly Chart Data
        $weeklyChart = [];
        $maxRevenue = 0;
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            $dailySum = Booking::where('station_id', $stationId)
                ->whereIn('status', $validStatuses) // Hitung booked & selesai
                ->whereDate('booking_date', $date->format('Y-m-d'))
                ->sum('total_price');
            
            if ($dailySum > $maxRevenue) $maxRevenue = $dailySum;

            $weeklyChart[] = [
                'day' => $date->locale('id')->isoFormat('ddd'), 
                'raw_value' => (int) $dailySum,
                'label' => ($dailySum / 1000) . 'rb'
            ];
        }

        $finalChart = array_map(function($item) use ($maxRevenue) {
            $percentage = $maxRevenue > 0 ? ($item['raw_value'] / $maxRevenue) * 100 : 0;
            return array_merge($item, ['value' => $percentage]);
        }, $weeklyChart);

        return Inertia::render('Host/Dashboard', [
            'station' => $station,
            'stats' => [
                'total_revenue' => $totalRevenue,
                'month_revenue' => $currentMonthRevenue,
                'growth' => $growth
            ],
            'recent_guests' => $recentGuests,
            'weekly_chart' => $finalChart,
        ]);
    }

    // Toggle Status Method
    public function toggleStatus(Request $request)
    {
        $request->validate([
            'is_open' => 'required|boolean'
        ]);

        $user = auth()->user();
        $station = Station::where('user_id', $user->id)->first();

        if ($station) {
            $station->update([
                'is_open' => $request->is_open,
                'status' => $request->is_open ? 'Tersedia' : 'Tutup'
            ]);
        }

        return back();
    }

    // Show Guest Details Method
    public function showGuest($id)
    {
        $user = auth()->user();
        $station = Station::where('user_id', $user->id)->first();

        if (!$station) {
            return redirect()->route('host.dashboard');
        }

        $guest = User::findOrFail($id);

        // Ubah pencarian pakai station_id agar konsisten
        $bookingsQuery = Booking::where('station_id', $station->id)
            ->where('user_id', $id);

        // Statistics (Hitung Booked + Selesai sebagai total spent)
        $totalSpent = (clone $bookingsQuery)->whereIn('status', ['Booked', 'Selesai'])->sum('total_price');
        $totalVisits = (clone $bookingsQuery)->count();
        $lastVisitDate = (clone $bookingsQuery)->latest('booking_date')->value('booking_date');

        $history = $bookingsQuery->orderBy('booking_date', 'desc')
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'date' => Carbon::parse($booking->booking_date)->format('d M Y, H:i'),
                    'amount' => $booking->total_price,
                    'status' => $booking->status,
                    'duration' => $booking->duration . ' Menit'
                ];
            });

        return Inertia::render('Host/GuestDetail', [
            'guest' => $guest,
            'history' => $history,
            'stats' => [
                'total_spent' => $totalSpent,
                'total_visits' => $totalVisits,
                'last_visit' => $lastVisitDate ? Carbon::parse($lastVisitDate)->diffForHumans() : 'Belum pernah',
            ]
        ]);
    }
}