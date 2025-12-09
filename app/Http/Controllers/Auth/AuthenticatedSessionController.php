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
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Ambil data user yang baru login
        $user = $request->user();

        // --- LOGIKA PENGALIHAN (TRAFFIC LIGHT) ---
        
        // 1. JALUR KHUSUS ADMIN (Hard Redirect)
        // Kita gunakan ->route() agar MEMAKSA pindah, mengabaikan history 'intended'
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // 2. JALUR KHUSUS OPERATOR (Hard Redirect)
        // Ini memperbaiki masalah Operator nyasar ke Dashboard User
        if ($user->role === 'operator') {
            return redirect()->route('operator.dashboard');
        }

        // 3. JALUR UMUM (USER BIASA)
        // Gunakan intended() agar user dikembalikan ke halaman terakhir yang dia buka (UX bagus)
        // Jika tidak ada history, default ke 'dashboard'
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}