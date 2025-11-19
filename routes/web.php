<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Landing & Authentication Routes
|--------------------------------------------------------------------------
| Rute-rute untuk halaman utama dan form autentikasi (Login/Signup).
|
*/

// Landing Page (Halaman Utama)
Route::get('/', function () {
    return Inertia::render('LandingPage');
    })->name('welcome');

// Rute Register
Route::get('/register', function () {
    return Inertia::render('Register');
    })->name('register');

// Rute Login
Route::get('/login', function () {
    return Inertia::render('Login');
    })->name('login');


/*
|--------------------------------------------------------------------------
| Navigation & Info Pages Routes
|--------------------------------------------------------------------------
| Rute-rute untuk halaman informasi yang ada di navbar.
|
*/

Route::get('/about', function () {
    return Inertia::render('AboutUs');
    })->name('about');

Route::get('/contact', function () {
    return Inertia::render('ContactUs');
})->name('contact');


/*
|--------------------------------------------------------------------------
| User, Admin, Operator & Utility Routes
|--------------------------------------------------------------------------
| Rute yang memerlukan autentikasi atau bersifat utilitas.
|
*/

// Dashboard (Diasumsikan butuh auth di implementasi akhir)
Route::get('/dashboard', function () {
    return Inertia::render('user/UserDashboard');
    })->name('dashboard');

// Profil Pengguna (Diasumsikan butuh auth)
Route::get('/user-profile', function () {
    return Inertia::render('user/UserProfile');
    })->name('user-profile');

// Halaman Hasil Peta (Simulasi Pencarian)
Route::get('/map-results', function () {
    return Inertia::render('user/MapResults');
    })->name('map.results');



// Halaman Admin
Route::get('/admin/profile', function () {
    return Inertia::render('admin/AdminProfile');
    })->name('admin-profile');

Route::get('/services', function () {
    return view('services'); // atau arahkan ke controller jika ada
    })->name('services');

// Halaman Admin Dashboard
Route::get('/admin-dashboard', function () {
    return Inertia::render('admin/AdminDashboard');
    })->name('admin.dashboard');

// Halaman Operator
Route::get('/operator', function () {
    return Inertia::render('Operator');
    })->name('operator');

// Route untuk halaman cetak struk
Route::get('/print-struk', function () {
    $station = request('station');
    $total   = request('total');
    return Inertia::render('PrintStrukPembayaran', [
        'station' => $station,
        'total'   => $total
    ]);
    })->name('print.struk');


// Memasukkan rute-rute otentikasi standar dari Laravel Breeze (jika ada)
require __DIR__ . '/auth.php';
