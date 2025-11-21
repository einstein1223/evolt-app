<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController; // Pastikan ini ada
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Landing & Authentication
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('LandingPage');
})->name('welcome');

/*
|--------------------------------------------------------------------------
| Info Pages
|--------------------------------------------------------------------------
*/

Route::get('/about', function () {
    return Inertia::render('AboutUs');
})->name('about');

Route::get('/contact', function () {
    return Inertia::render('ContactUs');
})->name('contact');

/*
|--------------------------------------------------------------------------
| USER (LOGIN DIPERLUKAN)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    
    // 1. Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('user/UserDashboard');
    })->name('dashboard');

    // 2. Halaman Profile (GABUNGAN DARI 2 KODE KAMU SEBELUMNYA)
    Route::get('/user-profile', function () {
        return Inertia::render('user/UserProfile', [
            'mustVerifyEmail' => request()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail,
            'status' => session('status'),
            // Ambil riwayat booking (PENTING: Relasi harus ada di Model User)
            'orders' => request()->user()->bookings 
        ]);
    })->name('user.profile');

    // 3. Update & Delete Profile
    Route::patch('/user-profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/user-profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 4. Map Results
    Route::get('/map-results', function () {
        return Inertia::render('user/MapResults');
    })->name('map.results');

    // 5. Simpan Booking (WAJIB DI DALAM AUTH AGAR TAHU USER ID)
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
});

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', function () {
        return Inertia::render('admin/AdminDashboard');
    })->name('admin.dashboard');

    Route::get('/admin/profile', function () {
        return Inertia::render('admin/AdminProfile');
    })->name('admin.profile');
});

/*
|--------------------------------------------------------------------------
| OPERATOR
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'operator'])->group(function () {
    Route::get('/operator', function () {
       return Inertia::render('operators/Operator');
    })->name('operator.dashboard');
});

/*
|--------------------------------------------------------------------------
| CETAK STRUK
|--------------------------------------------------------------------------
*/

Route::get('/print-struk', function () {
    return Inertia::render('PrintStrukPembayaran', [
        'station' => request('station'),
        'total'   => request('total')
    ]);
})->name('print.struk');

/*
|--------------------------------------------------------------------------
| Auth routes (Laravel Breeze)
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';