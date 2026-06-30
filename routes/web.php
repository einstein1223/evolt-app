<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\MapResultController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\HostController;
use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Booking;

/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => Inertia::render('LandingPage'))->name('welcome');
Route::get('/about', fn() => Inertia::render('AboutUs'))->name('about');
Route::get('/contact', fn() => Inertia::render('ContactUs'))->name('contact');
Route::get('/gabung-mitra', fn() => Inertia::render('GabungMitra'))->name('gabung.mitra');
Route::get('/ev-pedia', fn() => Inertia::render('EVPedia'))->name('ev.pedia');

// ✅ PENTING: URL harus /paymenku/webhook (bukan /paymentku)
// URL ini yang dikirim sebagai callback_url ke Paymenku saat create transaksi
Route::post('/paymenku/webhook', [BookingController::class, 'handleWebhook'])
    ->name('paymenku.webhook')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

/*
|--------------------------------------------------------------------------
| 2. GENERAL AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/scan-qr', fn() => Inertia::render('user/ScanQr'))->name('scan.qr');
    Route::get('/history', [BookingController::class, 'history'])->name('history');
    Route::get('/map-results', [MapResultController::class, 'index'])->name('map.results');

    // ─── BOOKING ──────────────────────────────────────────────────────────────
    Route::get('/booking/slots',            [BookingController::class, 'slots'])          ->name('booking.slots');
    Route::get('/booking/payment-channels', [BookingController::class, 'paymentChannels'])->name('booking.payment-channels');
    Route::post('/booking',                 [BookingController::class, 'store'])          ->name('booking.store');
    Route::get('/booking/check-status/{bookingCode}', [BookingController::class, 'checkStatus'])->name('booking.check-status');
    // ─────────────────────────────────────────────────────────────────────────

    Route::get('/status-charging', function () {
        $booking = Booking::where('user_id', auth()->id())->latest()->first();
        return Inertia::render('user/StatusCharging', [
            'stationName' => $booking?->station_name ?? '-',
            'location'    => $booking?->location     ?? '-',
            'duration'    => $booking?->duration     ?? 0,
            'power'       => '50 kW',
            'type'        => $booking?->port_type    ?? '-',
        ]);
    })->name('status.charging');

    Route::get('/profile',        [ProfileController::class, 'edit'])    ->name('profile');
    Route::get('/user/profile',   [ProfileController::class, 'edit'])    ->name('profile.edit');
    Route::patch('/user/profile', [ProfileController::class, 'update'])  ->name('profile.update');
    Route::delete('/user/profile',[ProfileController::class, 'destroy']) ->name('profile.destroy');
    Route::put('/password',       [PasswordController::class, 'update']) ->name('password.update');

    Route::get('/print-struk', function () {
        return Inertia::render('PrintStrukPembayaran', [
            'station' => request('station'),
            'total'   => request('total'),
        ]);
    })->name('print.struk');
});

/*
|--------------------------------------------------------------------------
| 3. ROLE SPECIFIC ROUTES
|--------------------------------------------------------------------------
*/

// MITRA HOST
Route::middleware(['auth', 'role:host'])->group(function () {
    Route::get('/host-dashboard',       [HostController::class, 'index'])           ->name('host.dashboard');
    Route::post('/host/station',        [HostController::class, 'storeStation'])    ->name('host.station.store');
    Route::post('/host/toggle-status',  [HostController::class, 'toggleStatus'])    ->name('host.toggle');
    Route::get('/host/guest/{userId}',  [HostController::class, 'showGuest'])       ->name('host.guest.detail');
    Route::get('/host/chart-data',      [HostController::class, 'chartData'])       ->name('host.chart');
    Route::delete('/host/booking/{id}', [BookingController::class, 'destroyByHost'])->name('host.booking.destroy');
});

// OPERATOR
Route::middleware(['auth', 'role:operator'])->group(function () {
    Route::get('/operator-area', [OperatorController::class, 'index'])->name('operator.dashboard');
});

// ADMIN
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin-dashboard',       [AdminController::class, 'index'])          ->name('admin.dashboard');
    Route::post('/admin/station',        [AdminController::class, 'storeStation'])   ->name('admin.station.store');
    Route::delete('/admin/station/{id}', [AdminController::class, 'destroyStation']) ->name('admin.station.destroy');
    Route::post('/admin/brand',          [AdminController::class, 'storeBrand'])     ->name('admin.brand.store');
    Route::post('/admin/type',           [AdminController::class, 'storeType'])      ->name('admin.type.store');
});

require __DIR__ . '/auth.php';