<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule; 
use App\Models\User; // Pastikan Import Model User
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman edit profile.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('user/UserProfile', [ // Sesuaikan path jika foldernya beda
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update data profil user.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        // 1. VALIDASI DATA
        // Kita pastikan data yang dikirim sesuai aturan
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            
            // Email harus unique, TAPI abaikan (ignore) kalau itu email milik user ini sendiri
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            
            // Validasi Custom Field E-VOLT
            'nomor_plat' => ['required', 'string', 'max:20'], 
            'nomor_telepon' => ['required', 'string', 'max:15'],
            
            // Validasi Field Opsional (Boleh kosong/nullable)
            'gender' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'birthDate' => ['nullable', 'date'],
            'idType' => ['nullable', 'string'],
            'idNumber' => ['nullable', 'string'],
        ]);

        // 2. MASUKKAN DATA KE MODEL
        $user->fill($validated);

        // Jika email diganti, reset status verifikasinya
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // 3. SIMPAN KE DATABASE
        $user->save();

        // 4. REDIRECT KEMBALI DENGAN PESAN
        return Redirect::route('user.profile')->with('message', 'Profile berhasil diupdate!');
    }

    /**
     * Delete account (Biarkan default jika belum mau dipakai)
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}