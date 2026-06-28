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
use App\Models\Booking;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil + riwayat booking user
     */
    public function edit(Request $request): Response
    {
        // Ambil semua booking milik user yang sedang login, urutkan terbaru
        $bookings = Booking::where('user_id', Auth::id())
                        ->orderBy('booking_date', 'desc')
                        ->get();

        return Inertia::render('user/UserProfile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status'          => session('status'),
            'bookings'        => $bookings, // <-- kirim sebagai 'bookings'
        ]);
    }

    /**
     * Update data profil user
     */
    public function update(Request $request): RedirectResponse
    {
        // 1. Validasi Input
        $request->validate([
            'username'         => ['sometimes', 'string', 'max:255'],
            'email'            => ['sometimes', 'email', 'max:255', Rule::unique(User::class)->ignore($request->user()->id)],
            'brand'            => ['nullable', 'string', 'max:50'],
            'series'           => ['nullable', 'string', 'max:50'],
            'variant'          => ['nullable', 'string', 'max:50'],
            'battery_capacity' => ['nullable', 'numeric'],
            'max_range'        => ['nullable', 'numeric'],
            'nomor_plat'       => ['nullable', 'string', 'max:20'],
            'car_brand'        => ['nullable', 'string', 'max:50'],
            'nomor_telepon'    => ['nullable', 'string', 'max:15'],
            'city'             => ['nullable', 'string'],
            'gender'           => ['nullable', 'string'],
            'birthDate'        => ['nullable', 'string'],
        ]);

        $user = $request->user();

        // 2. Isi data standar
        $user->fill($request->only([
            'username', 'email', 'nomor_plat', 'nomor_telepon',
            'city', 'gender', 'birthDate', 'battery_capacity', 'max_range'
        ]));

        // 3. Mapping manual field kendaraan (dari UserDashboard maupun UserProfile)
        if ($request->has('brand')) {
            $user->car_brand = $request->input('brand');
        } elseif ($request->has('car_brand')) {
            $user->car_brand = $request->input('car_brand');
        }

        if ($request->has('series')) {
            $user->car_series = $request->input('series');
        } elseif ($request->has('car_series')) {
            $user->car_series = $request->input('car_series');
        }

        if ($request->has('variant')) {
            $user->car_type = $request->input('variant');
        } elseif ($request->has('car_type')) {
            $user->car_type = $request->input('car_type');
        }

        if ($request->has('plateNumber')) {
            $user->nomor_plat = $request->input('plateNumber');
        }

        // 4. Reset verifikasi email jika email diubah
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // 5. Simpan
        $user->save();

        return Redirect::back()->with('message', 'Profil berhasil diperbarui!');
    }

    /**
     * Hapus akun user
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
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