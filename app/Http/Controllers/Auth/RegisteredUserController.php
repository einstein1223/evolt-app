<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



class RegisteredUserController extends Controller
{
    /**
     * Menampilkan halaman registrasi.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Menangani permintaan registrasi yang masuk.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
{
    // 1. VALIDASI
    $request->validate([
        'username'      => 'required|string|max:255|unique:users,username',
        'email'         => 'required|string|email|max:255|unique:users,email',
        // GANTI max:9 JADI max:20 biar aman (termasuk spasi)
        'nomor_plat'    => 'required|string|max:20|unique:users,nomor_plat', 
        'nomor_telepon' => 'required|string|max:15', // 13 kadang mepet kalau ada +62
        'password'      => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    // HAPUS dd($request->all()); AGAR KODE LANJUT KE BAWAH

    // 2. SIMPAN KE DATABASE
    User::create([
        'username'      => $request->username,
        'email'         => $request->email,
        'nomor_plat'    => $request->nomor_plat,
        'nomor_telepon' => $request->nomor_telepon,
        'password'      => Hash::make($request->password),
        'role'          => 'user', // Default role
    ]);

    // 3. REDIRECT KE LOGIN
    // Menggunakan 'with' agar bisa menampilkan pesan sukses di halaman login (opsional)
    return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login.');
}



        // 4. (DIHAPUS) KITA TIDAK LOGIN-KAN USER SECARA OTOMATIS
        // Auth::login($user); // <-- Baris ini dinonaktifkan

        // 5. KIRIM RESPON REDIRECT KE HALAMAN LOGIN
        // Inertia akan menangkap redirect ini dan mengarahkan
        // frontend (Vue) ke halaman login.
     
}



