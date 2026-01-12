<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Station;
use App\Models\Booking;
use App\Models\User; // Tambahkan import User
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HostController extends Controller
{
    /**
     * Menampilkan Dashboard Khusus Host
     */
    public function index()
    {
        $user = Auth::user();
        
        // 1. Ambil data stasiun milik user ini
        $myStation = Station::where('user_id', $user->id)->first();

        // 2. JIKA DATA KOSONG (User belum daftar jadi host)
        if (!$myStation) {
            return Inertia::render('Host/Dashboard', [
                'station' => null,
                'stats' => ['total_revenue' => 0, 'month_revenue' => 0, 'growth' => 0],
                'recent_guests' => []
            ]);
        }

        // 3. AMBIL DATA BOOKING
        $bookings = Booking::with('user')
                           ->where('station_name', $myStation->name)
                           ->latest()
                           ->get();
        
        // --- LOGIKA STATISTIK ---
        $finishedBookings = $bookings->where('status', 'Selesai');
        
        // Total Pendapatan
        $totalRevenue = $finishedBookings->sum('total_price');
        
        // Pendapatan Bulan Ini
        $thisMonthRevenue = $finishedBookings
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->sum('total_price');

        // Pendapatan Bulan Lalu (Untuk hitung Growth %)
        $lastMonthRevenue = $finishedBookings
            ->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
            ->sum('total_price');

        // Hitung Growth (Kenaikan %)
        $growth = 0;
        if ($lastMonthRevenue > 0) {
            $growth = (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100;
        } else if ($thisMonthRevenue > 0) {
            $growth = 100;
        }

        // --- MAPPING DATA TAMU ---
        $recentGuests = $bookings->take(10)->map(function($b) {
            $user = $b->user;
            
            // Nama Tamu
            $guestName = $user ? ($user->username ?? $user->name) : 'Tamu (Dihapus)';

            // Data Mobil
            $carName = 'Kendaraan Listrik';
            $platNumber = '-';

            if ($user) {
                if (!empty($user->car_brand)) {
                    $carName = $user->car_brand . ' ' . ($user->car_series ?? '');
                }
                $platNumber = $user->nomor_plat ?? '-';
            }

            return [
                'id' => $b->id,
                'user_id' => $b->user_id, // <--- PENTING: ID User untuk link detail
                'guest_name' => $guestName, 
                'car' => $carName,
                'plat' => $platNumber, 
                'date' => Carbon::parse($b->created_at)->locale('id')->diffForHumans(),
                'amount' => $b->total_price,
                'status' => $b->status
            ];
        });

        // 4. RENDER VUE
        return Inertia::render('Host/Dashboard', [
            'station' => $myStation,
            'stats' => [
                'total_revenue' => $totalRevenue,
                'month_revenue' => $thisMonthRevenue,
                'growth' => round($growth, 1)
            ],
            'recent_guests' => $recentGuests
        ]);
    }

    /**
     * Menampilkan Detail Tamu & Riwayat Transaksi di Station Ini
     * (Method Baru untuk CRM Sederhana)
     */
    public function showGuest($userId)
    {
        // 1. Ambil Data Station Milik Host (Security Check)
        $myStation = Station::where('user_id', Auth::id())->firstOrFail();
        
        // 2. Ambil Data Tamu
        $guest = User::findOrFail($userId);

        // 3. Ambil Riwayat Booking Tamu INI di Station INI saja
        $history = Booking::where('user_id', $userId)
                          ->where('station_name', $myStation->name)
                          ->latest()
                          ->get()
                          ->map(function($b) {
                              return [
                                  'id' => $b->id,
                                  'date' => Carbon::parse($b->created_at)->format('d M Y, H:i'),
                                  'duration' => $b->duration . ' Menit',
                                  'amount' => $b->total_price,
                                  'status' => $b->status,
                                  'port_type' => $b->port_type ?? 'Type 2'
                              ];
                          });

        // 4. Hitung Statistik Tamu Ini
        $totalSpent = $history->where('status', 'Selesai')->sum('amount');
        $totalVisits = $history->count();

        return Inertia::render('Host/GuestDetail', [
            'guest' => [
                'name' => $guest->name,
                'email' => $guest->email,
                'phone' => $guest->nomor_telepon ?? '-',
                'car' => ($guest->car_brand ?? '-') . ' ' . ($guest->car_series ?? ''),
                'plat' => $guest->nomor_plat ?? '-',
                'join_date' => Carbon::parse($guest->created_at)->format('d M Y'),
                'avatar_letter' => strtoupper(substr($guest->name, 0, 1)),
            ],
            'history' => $history,
            'stats' => [
                'total_visits' => $totalVisits,
                'total_spent' => $totalSpent
            ]
        ]);
    }

    /**
     * Toggle Buka/Tutup (Sinkronisasi Database)
     */
    public function toggleStatus(Request $request)
    {
        $request->validate([
            'is_open' => 'required|boolean'
        ]);

        $station = Station::where('user_id', Auth::id())->firstOrFail();
        
        $station->update([
            'is_open' => $request->is_open,
            'status'  => $request->is_open ? 'Tersedia' : 'Tutup'
        ]);

        return back();
    }
}