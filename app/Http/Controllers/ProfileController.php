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
        // Ambil semua booking milik user yang login, urutkan terbaru
        // ✅ Pilih kolom yang dibutuhkan Vue saja agar tidak ada field null
        $bookings = Booking::where('user_id', Auth::id())
            ->orderBy('booking_date', 'desc')
            ->get([
                'id',
                'booking_code',
                'station_name',
                'location',
                'booking_date',
                'booking_slot',
                'duration',
                'total_price',
                'status',
                'port_type',
                'plate_number',
                'end_time',
            ])
            ->map(fn($b) => [
                'id'            => $b->id,
                // ✅ Mapping field → sesuaikan dengan yang dipakai Vue (booking_code, station_name, dll)
                'booking_code'  => $b->booking_code,
                'station_name'  => $b->station_name  ?? 'Stasiun Pengisian',
                'location'      => $b->location      ?? '-',
                'booking_date'  => $b->booking_date,
                'booking_slot'  => $b->booking_slot  ?? null,
                'duration'      => $b->duration      ?? 0,
                'total_price'   => $b->total_price   ?? 0,
                'status'        => $b->status        ?? 'Menunggu Pembayaran',
                'port_type'     => $b->port_type     ?? '-',
                'plate_number'  => $b->plate_number  ?? Auth::user()->nomor_plat ?? '-',
                'end_time'      => $b->end_time      ?? null,
                'created_at'    => $b->booking_date, // Vue pakai created_at untuk display tanggal
            ]);

        return Inertia::render('user/UserProfile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status'          => session('status'),
            'bookings'        => $bookings,
        ]);
    }

    /**
     * Update data profil user
     */
    public function update(Request $request): RedirectResponse
    {
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

        $user->fill($request->only([
            'username', 'email', 'nomor_plat', 'nomor_telepon',
            'city', 'gender', 'birthDate', 'battery_capacity', 'max_range'
        ]));

        // Mapping field kendaraan (dua kemungkinan nama field dari frontend)
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

        // Reset verifikasi email jika email diubah
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

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