<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
{
    $request->validate([
        'username' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'nomor_telepon' => 'required|string|max:15',
        'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
    ]);

    $user = \App\Models\User::create([
        'username' => $request->username,
        'email' => $request->email,
        'nomor_telepon' => $request->nomor_telepon,
        'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        'role' => 'user',
    ]);

    event(new Registered($user));

    // --- [HAPUS ATAU KOMENTARI BARIS INI] ---
    // Auth::login($user); 
    // ----------------------------------------

    // Ubah Redirect ke halaman Login dengan pesan sukses
    return redirect(route('login'))->with('message', 'Registrasi Berhasil! Silakan Login.');
}
}