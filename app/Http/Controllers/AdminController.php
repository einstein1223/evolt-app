<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Station;
use App\Models\Brand;
use App\Models\CarType;
use App\Models\User;     // Tambahkan ini
use App\Models\Booking;  // Tambahkan ini
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;       // Untuk olah tanggal

class AdminController extends Controller
{
    public function index()
    {
        // 1. Ambil Data Pengguna (Hanya Role User)
        // Kita hitung total charging & cek kapan terakhir aktif
        $userReports = User::where('role', 'user')->get()->map(function ($user) {
            $lastBooking = Booking::where('user_id', $user->id)->latest()->first();
            $totalCharges = Booking::where('user_id', $user->id)->count();
            
            // Tentukan status berdasarkan aktivitas terakhir (misal: aktif jika booking < 30 hari lalu)
            $status = 'Baru';
            if ($lastBooking) {
                $daysSinceLast = Carbon::parse($lastBooking->created_at)->diffInDays(now());
                $status = $daysSinceLast < 30 ? 'Aktif' : 'Tidak Aktif';
            }

            return [
                'id' => $user->id,
                'name' => $user->name ?: $user->username, // Fallback jika name null
                'email' => $user->email,
                'totalCharges' => $totalCharges,
                'lastActive' => $lastBooking ? Carbon::parse($lastBooking->created_at)->format('d M Y') : '-',
                'status' => $status,
            ];
        });

        // 2. Ambil Data Laporan Operator (Dari Booking)
        // Kita kelompokkan booking berdasarkan Station dan Bulan
        // Note: Ini logika sederhana, untuk produksi skala besar sebaiknya pakai query aggregate SQL
        $stations = Station::all();
        $operatorReports = [];
        $reportId = 1;

        foreach ($stations as $station) {
            // Ambil semua booking untuk stasiun ini
            $bookings = Booking::where('station_name', $station->name)->get();
            
            if ($bookings->count() > 0) {
                // Hitung total pendapatan & sesi
                $totalRevenue = $bookings->sum('total_price');
                $totalSessions = $bookings->count();
                
                // Ambil bulan transaksi terakhir sebagai periode laporan (Simulasi Mingguan/Bulanan)
                $lastTx = $bookings->last();
                $date = Carbon::parse($lastTx->created_at);

                $operatorReports[] = [
                    'id' => $reportId++,
                    'stationName' => $station->name,
                    'owner' => 'E-VOLT Corp', // Bisa diambil dari relasi owner jika ada
                    'domicile' => $station->city ?? 'Batam',
                    'week' => 'Minggu ' . $date->weekOfMonth,
                    'month' => $date->translatedFormat('F'), // Nama Bulan (Januari, dsb)
                    'year' => $date->format('Y'),
                    'totalSessions' => $totalSessions,
                    'revenue' => $totalRevenue, // Kirim angka murni
                    'status' => 'Terkirim' // Status dummy laporan
                ];
            }
        }

        // 3. Kirim Semua Data ke Vue
        return Inertia::render('admin/AdminDashboard', [
            'stations' => Station::latest()->get(),
            'brands' => Brand::all(),
            'types' => CarType::all(),
            
            // Data Baru Hasil Integrasi
            'dbUserReports' => $userReports,
            'dbOperatorReports' => $operatorReports
        ]);
    }

    // ... (Method storeStation, destroyStation, dll tetap sama di bawah sini) ...
    
    public function storeStation(Request $request)
    {
        $request->validate([
            'name' => 'required', 'latitude' => 'required', 'longitude' => 'required', 'city' => 'required', 'chargers' => 'required|array',
        ]);

        Station::create([
            'name' => $request->name,
            'city' => $request->city,
            'operational_hours' => $request->operationalHours ?? '24/7',
            'lat' => $request->latitude,
            'lng' => $request->longitude,
            'address' => $request->latitude . ', ' . $request->longitude,
            'chargers_detail' => $request->chargers,
            'status' => 'Aktif'
        ]);

        return Redirect::back()->with('message', 'Stasiun Berhasil Ditambahkan!');
    }

    public function destroyStation($id)
    {
        Station::findOrFail($id)->delete();
        return Redirect::back()->with('message', 'Stasiun Dihapus.');
    }
    
    public function storeBrand(Request $request) { Brand::create(['name' => $request->name]); return Redirect::back(); }
    public function storeType(Request $request) { CarType::create(['name' => $request->name]); return Redirect::back(); }
}