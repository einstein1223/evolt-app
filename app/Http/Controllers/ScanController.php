<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log; // Tambahkan Log untuk debugging

class ScanController extends Controller
{
    // Tampilkan Halaman Scanner
    public function index()
    {
        return Inertia::render('ScanStation');
    }

    // Proses Verifikasi QR
    public function verify(Request $request)
    {
        $rawToken = $request->token; // Token yang dikirim dari kamera (frontend)
        
        // 1. Validasi Format Token
        // Token harus diawali dengan "ACCESS_TOKEN_"
        if (!str_starts_with($rawToken, 'ACCESS_TOKEN_')) {
            return response()->json([
                'status' => 'error',
                'message' => 'QR Code Tidak Valid! Pastikan Anda menscan QR Akses, bukan QR Pembayaran.'
            ], 400);
        }

        // 2. Bersihkan Token (Ambil Booking Number)
        // Contoh: "ACCESS_TOKEN_BK-1732..." menjadi "BK-1732..."
        $bookingNumber = str_replace('ACCESS_TOKEN_', '', $rawToken);

        // 3. Cari di Database
        $booking = Booking::where('booking_number', $bookingNumber)->first();

        if (!$booking) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data Booking Tidak Ditemukan! (Kode: ' . $bookingNumber . ')'
            ], 404);
        }

        // 4. Cek Status Pembayaran
        if ($booking->status !== 'Selesai') { 
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi Belum Lunas! Status saat ini: ' . $booking->status
            ], 400);
        }

        // 5. Sukses
        return response()->json([
            'status' => 'success',
            'message' => 'Akses Diterima!',
            'data' => [
                // Ambil nama user dari relasi user
                'user' => $booking->user ? $booking->user->name : 'Pengguna Tamu',
                'station' => $booking->station_name,
                'duration' => $booking->duration
            ]
        ]);
    }
}