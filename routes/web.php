<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController; 
use App\Http\Controllers\MapResultController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OperatorController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES (Tanpa Login)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('LandingPage');
})->name('welcome');

Route::get('/about', function () {
    return Inertia::render('AboutUs');
})->name('about');

Route::get('/contact', function () {
    return Inertia::render('ContactUs');
})->name('contact');

/*
|--------------------------------------------------------------------------
| 2. USER ROUTES (WAJIB LOGIN)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    
    // --- DASHBOARD ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- MAP & BOOKING ---
    Route::get('/map-results', [MapResultController::class, 'index'])->name('map.results');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');

    // --- USER PROFILE ---
    Route::get('/user-profile', function () {
        return Inertia::render('user/UserProfile', [
            'mustVerifyEmail' => request()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail,
            'status' => session('status'),
            'orders' => request()->user()->bookings, 
        ]);
    })->name('user.profile');

    // Update Data Kendaraan (Pop-up Dashboard)
    Route::post('/profile/vehicle', [ProfileController::class, 'updateVehicle'])->name('profile.vehicle.update');

    // Update & Hapus Akun
    Route::patch('/user-profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/user-profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- CETAK STRUK ---
    Route::get('/print-struk', function () {
        return Inertia::render('PrintStrukPembayaran', [
            'station' => request('station'),
            'total'   => request('total')
        ]);
    })->name('print.struk');

    // --- BECOME A HOST (CHARGER TETANGGA) ---
    // Pindahkan ke dalam sini agar aman
    Route::get('/become-host', function () {
        return Inertia::render('user/BecomeHost');
    })->name('become.host');
});

/*
|--------------------------------------------------------------------------
| 3. ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->group(function () {
    
    // Dashboard Admin
    Route::get('/admin-dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    // Manajemen Stasiun
    Route::post('/admin/station', [AdminController::class, 'storeStation'])->name('admin.station.store');
    Route::delete('/admin/station/{id}', [AdminController::class, 'destroyStation'])->name('admin.station.destroy');
    
    // Manajemen Master Data
    Route::post('/admin/brand', [AdminController::class, 'storeBrand'])->name('admin.brand.store');
    Route::post('/admin/type', [AdminController::class, 'storeType'])->name('admin.type.store');
    
    // Profile Admin
    Route::get('/admin/profile', function () {
        return Inertia::render('admin/AdminProfile');
    })->name('admin.profile');
});

/*
|--------------------------------------------------------------------------
| 4. OPERATOR ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'operator'])->group(function () {
    
    Route::get('/operator-area', [OperatorController::class, 'index'])->name('operator.dashboard');
});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';