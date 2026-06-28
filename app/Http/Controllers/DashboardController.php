<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Station; // <-- WAJIB TAMBAHKAN INI

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Cek Apakah User adalah HOST?
        if ($user->role === 'host') { 
            return redirect()->route('host.dashboard');
        }

        // 2. Cek Apakah User adalah ADMIN?
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // 3. Cek Apakah User adalah OPERATOR?
        if ($user->role === 'operator') {
            return redirect()->route('operator.dashboard');
        }

        // 4. JIKA USER BIASA
        // Ambil semua data stasiun dari database
        $stations = Station::all(); 

        return Inertia::render('user/UserDashboard', [ 
            'stations' => $stations // <-- KIRIM DATANYA KE VUE DI SINI
        ]);
    }
}