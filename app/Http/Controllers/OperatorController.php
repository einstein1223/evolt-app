<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Booking;
use App\Models\Station;
use App\Models\User;
use Carbon\Carbon;

class OperatorController extends Controller
{
    public function index()
    {
        // --- 1. STATISTIK ATAS ---
        $totalStations = Station::count();
        
        // Hitung stasiun aktif
        $activeStations = Station::where('status', 'Tersedia')->orWhere('status', 'Aktif')->count();
        
        // Hitung booking bulan ini
        $currentMonthBookings = Booking::whereMonth('created_at', Carbon::now()->month)
                                       ->whereYear('created_at', Carbon::now()->year);
        $totalSessionsMonth = $currentMonthBookings->count();
        
        // Total User
        $totalUsers = User::where('role', 'user')->count();

        // --- 2. TABEL LAPORAN OPERATOR MINGGUAN (SAMA DENGAN ADMIN) ---
        // Mengelompokkan booking berdasarkan stasiun untuk melihat performa/pendapatan
        $stations = Station::all();
        $operatorReports = [];
        $reportId = 1;

        foreach ($stations as $station) {
            $bookings = Booking::where('station_name', $station->name)->get();
            
            if ($bookings->count() > 0) {
                $totalRevenue = $bookings->sum('total_price');
                $totalSessions = $bookings->count();
                $lastTx = $bookings->last();
                $date = Carbon::parse($lastTx->created_at);

                $operatorReports[] = [
                    'id' => $reportId++,
                    'stationName' => $station->name,
                    'owner' => 'E-VOLT Corp',
                    'domicile' => $station->city ?? 'Batam',
                    'period' => 'Minggu ' . $date->weekOfMonth . ' (' . $date->translatedFormat('F Y') . ')',
                    'totalSessions' => $totalSessions,
                    'revenue' => $totalRevenue,
                    'status' => 'Terkirim',
                    'month' => $date->translatedFormat('F'),
                    'year' => $date->format('Y'),
                ];
            }
        }

        // --- 3. TABEL PENGGUNA TERDAFTAR ---
        $users = User::where('role', 'user')->latest()->get()->map(function($u) {
            $lastBooking = Booking::where('user_id', $u->id)->latest()->first();
            return [
                'id' => $u->id,
                'name' => $u->name ?: $u->username,
                'email' => $u->email,
                'totalCharges' => Booking::where('user_id', $u->id)->count(),
                'lastActive' => $lastBooking ? Carbon::parse($lastBooking->created_at)->format('d M Y') : '-',
                'status' => 'Aktif'
            ];
        });

        return Inertia::render('operators/Operator', [
            'stats' => [
                'totalStations' => $totalStations,
                'activeStations' => $activeStations,
                'totalSessionsMonth' => $totalSessionsMonth,
                'totalUsers' => $totalUsers,
            ],
            'stations' => $stations, 
            'dbOperatorReports' => $operatorReports, // Data Laporan yang sudah di-aggregasi
            'dbUserReports' => $users
        ]);
    }
}