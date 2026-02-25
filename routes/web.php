<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController; 
use App\Http\Controllers\MapResultController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\ScanController; // Pastikan controller ini ada/terpakai
use App\Http\Controllers\HostController; 
use App\Http\Controllers\Auth\PasswordController; 
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Booking;

/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES (Bisa diakses siapa saja)
|--------------------------------------------------------------------------
*/
Route::get('/', function () { return Inertia::render('LandingPage'); })->name('welcome');
Route::get('/about', function () { return Inertia::render('AboutUs'); })->name('about');
Route::get('/contact', function () { return Inertia::render('ContactUs'); })->name('contact');
Route::get('/gabung-mitra', function () { return Inertia::render('GabungMitra'); })->name('gabung.mitra');
Route::get('/ev-pedia', function () { return Inertia::render('EVPedia'); })->name('ev.pedia');

/*
|--------------------------------------------------------------------------
| 2. GENERAL AUTHENTICATED ROUTES (Harus Login Dulu)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    
    // --- DASHBOARD ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- FITUR UTAMA USER (Scan, Map, Booking) ---
    Route::get('/scan-qr', function () {
        return Inertia::render('user/ScanQr');
    })->name('scan.qr');

    Route::get('/history', [BookingController::class, 'history'])->name('history');
    
    Route::get('/map-results', [MapResultController::class, 'index'])->name('map.results'); 
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    
    // --- STATUS CHARGING ---
    Route::get('/status-charging', function () {
        // Ambil booking terakhir user
        $booking = Booking::where('user_id', auth()->id())->latest()->first();
        
        // Perhatikan besar kecil huruf folder 'User' vs 'user' (Linux server case-sensitive)
        return Inertia::render('user/StatusCharging', [ 
            'stationName' => $booking ? $booking->station_name : '-',
            'location'    => $booking ? $booking->location : '-',
            'duration'    => $booking ? $booking->duration : 0,
            'power'       => '50 kW', 
            'type'        => $booking ? $booking->port_type : '-',
        ]);
    })->name('status.charging');

    // --- PROFILE ROUTES ---
    Route::get('user/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('user/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('user/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Update Password
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    // Print Struk
    Route::get('/print-struk', function () {
        return Inertia::render('PrintStrukPembayaran', [
            'station' => request('station'),
            'total'   => request('total')
        ]);
    })->name('print.struk');
});

/*
|--------------------------------------------------------------------------
| 3. ROLE SPECIFIC ROUTES (Dibatasi Role Tertentu)
|--------------------------------------------------------------------------
*/

// MITRA HOST
Route::middleware(['auth', 'role:host'])->group(function () {
    Route::get('/host-dashboard', [HostController::class, 'index'])->name('host.dashboard');
    Route::post('/host/toggle-status', [HostController::class, 'toggleStatus'])->name('host.toggle');
    Route::get('/host/guest/{userId}', [HostController::class, 'showGuest'])->name('host.guest.detail');
});

// OPERATOR
Route::middleware(['auth', 'role:operator'])->group(function () {
    Route::get('/operator-area', [OperatorController::class, 'index'])->name('operator.dashboard');
});

// ADMIN
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin-dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::post('/admin/station', [AdminController::class, 'storeStation'])->name('admin.station.store');
    Route::delete('/admin/station/{id}', [AdminController::class, 'destroyStation'])->name('admin.station.destroy');
    Route::post('/admin/brand', [AdminController::class, 'storeBrand'])->name('admin.brand.store');
    Route::post('/admin/type', [AdminController::class, 'storeType'])->name('admin.type.store');
});

require __DIR__ . '/auth.php';