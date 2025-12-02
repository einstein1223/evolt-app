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
    /**
     * Menampilkan halaman edit profile.
     */
    public function edit(Request $request): Response
    {
        // [PERBAIKAN] Hapus dd() ini agar halaman Profile BISA DIBUKA
        // dd($request->user()->bookings); 

        return Inertia::render('user/UserProfile', [ 
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'orders' => $request->user()->bookings, 
        ]);
    }

    /**
     * Update data profil user (Nama, Email, dll).
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($request->user()->id)],
            
            // Data Custom E-VOLT
            'nomor_plat' => ['required', 'string', 'max:20'],
            'nomor_telepon' => ['required', 'string', 'max:15'],

            // Data Opsional
            'gender' => ['nullable', 'string'],
            'birthDate' => ['nullable', 'date'],
            'city' => ['nullable', 'string'],
            'idType' => ['nullable', 'string'],
            'idNumber' => ['nullable', 'string'],
        ]);

        $request->user()->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('user.profile')->with('message', 'Profile berhasil diperbarui!');
    }

    /**
     * [BARU] Update data kendaraan user (Dipanggil dari Pop-up Dashboard).
     */
     public function updateVehicle(Request $request): RedirectResponse
    {

        $validated = $request->validate([
            'nomor_plat' => 'required|string|max:20',
            'car_brand'  => 'required|string',
            'car_type'   => 'required|string',
            'car_series' => 'required|string',
        ]);

        $request->user()->update($validated);

        return Redirect::back()->with('message', 'Data Kendaraan Berhasil Disimpan!');
    }

    /**
     * Delete account.
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