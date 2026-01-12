<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('user/UserProfile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'orders' => $request->user()->bookings,
        ]);
    }

    /**
     * Update data profil user
     */
    public function update(Request $request): RedirectResponse
    {
        // 1. Validasi Input
        // Kita tambahkan validasi untuk input nama dari Vue ('brand', 'series', dll)
        $request->validate([
            'username' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique(User::class)->ignore($request->user()->id)],
            
            // Input dari Vue (UserDashboard)
            'brand' => ['nullable', 'string', 'max:50'],
            'series' => ['nullable', 'string', 'max:50'],
            'variant' => ['nullable', 'string', 'max:50'],
            
            // Input Standar DB
            'nomor_plat' => ['nullable', 'string', 'max:20'],
            'car_brand' => ['nullable', 'string', 'max:50'],
            
            'nomor_telepon' => ['nullable', 'string', 'max:15'],
            'city' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        // 2. Isi Data Standar (Username, Email, dll)
        // fill() hanya akan mengisi jika nama input == nama kolom database
        $user->fill($request->only([
            'username', 'email', 'nomor_plat', 'nomor_telepon', 'city', 'gender', 'birthDate'
        ]));

        // 3. --- MAPPING MANUAL (PENTING) ---
        // Karena nama input di Vue beda dengan di DB, kita pasangkan manual di sini.
        
        // Vue kirim 'brand' -> Masuk ke DB 'car_brand'
        if ($request->has('brand')) {
            $user->car_brand = $request->input('brand');
        } elseif ($request->has('car_brand')) {
            $user->car_brand = $request->input('car_brand');
        }

        // Vue kirim 'series' -> Masuk ke DB 'car_series'
        if ($request->has('series')) {
            $user->car_series = $request->input('series');
        } elseif ($request->has('car_series')) {
            $user->car_series = $request->input('car_series');
        }

        // Vue kirim 'variant' -> Masuk ke DB 'car_type'
        if ($request->has('variant')) {
            $user->car_type = $request->input('variant');
        } elseif ($request->has('car_type')) {
            $user->car_type = $request->input('car_type');
        }
        
        // Khusus nomor plat (Vue UserDashboard sudah transform jadi 'nomor_plat', tapi jaga-jaga)
        if ($request->has('plateNumber')) {
            $user->nomor_plat = $request->input('plateNumber');
        }

        // 4. Reset Email Verification jika email ganti
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // 5. Simpan
        $user->save();

        return Redirect::back()->with('message', 'Profil berhasil diperbarui!');
    }

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