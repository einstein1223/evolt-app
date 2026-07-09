<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OtpController; // <--- PENTING: Import Controller ini
use App\Http\Controllers\BookingController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Route bawaan Laravel (biarkan saja)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ==========================================
// ROUTE OTP (Tambahkan ini)
// ==========================================
Route::post('/otp/send', [OtpController::class, 'send']);


Route::post('/paymenku/webhook', [BookingController::class, 'handleWebhook']);