<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Menampilkan Halaman Login
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Menangani Proses Login (PENTING DISINI)
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Validasi Email & Password
        $request->authenticate();

        // 2. Buat Session Baru
        $request->session()->regenerate();

        // 3. Ambil Data User yang Sedang Login
        $user = $request->user();

        // 4. Normalisasi Role (Hapus spasi, huruf kecil semua)
        // Jaga-jaga kalau di database tertulis "Host " atau "HOST"
        $role = strtolower(trim($user->role));

        // --- TRAFFIC LIGHT (PENGATUR LALU LINTAS) ---

        // A. JALUR ADMIN -> /admin-dashboard
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // B. JALUR OPERATOR -> /operator-area
        if ($role === 'operator') {
            return redirect()->route('operator.dashboard');
        }

        // C. JALUR HOST (MITRA) -> /host-dashboard
        // Pastikan di database kolom role isinya benar-benar 'host'
        if ($role === 'host') {
            return redirect()->route('host.dashboard');
        }

        // D. JALUR USER BIASA (DEFAULT) -> /dashboard
        // Jika tidak punya role khusus, lempar ke dashboard user biasa
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Logout
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}