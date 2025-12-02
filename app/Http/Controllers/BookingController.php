<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon; // PENTING: Library untuk olah waktu

class BookingController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Data Dasar
        $validated = $request->validate([
            'booking_number' => 'required',
            'station_name'   => 'required',
            'location'       => 'nullable',
            'port_type'      => 'nullable',
            'duration'       => 'required', // String "45 menit (20 kWh)"
            'total_price'    => 'required|numeric',
            'booking_time'   => 'required|date', // Data baru: Waktu booking spesifik
        ]);

        try {
            // 2. PARSING WAKTU UNTUK VALIDASI BENTROK
            // Kita harus tahu kapan booking ini Mulai (Start) dan Selesai (End)
            
            // Ambil durasi dalam angka (misal: "45 menit" -> 45)
            preg_match('/(\d+)\s*menit/', $validated['duration'], $matches);
            $durationMinutes = isset($matches[1]) ? (int)$matches[1] : 30; // Default 30 jika gagal parse

            // Waktu Mulai yang diminta user
            $requestedStart = Carbon::parse($validated['booking_time']);
            // Waktu Selesai (Mulai + Durasi)
            $requestedEnd = $requestedStart->copy()->addMinutes($durationMinutes);

            // 3. CEK APAKAH ADA BOOKING LAIN YANG TUMPANG TINDIH (OVERLAP)
            // Rumus Overlap: (StartA < EndB) AND (EndA > StartB)
            $conflict = Booking::where('station_name', $validated['station_name'])
                ->where('port_type', $validated['port_type'] ?? 'Regular') // Cek di port yang sama
                ->where('status', '!=', 'Batal') // Abaikan yang sudah batal
                ->get()
                ->filter(function ($existingBooking) use ($requestedStart, $requestedEnd) {
                    
                    // Parse waktu booking yang sudah ada di database
                    preg_match('/(\d+)\s*menit/', $existingBooking->duration, $m);
                    $existingDuration = isset($m[1]) ? (int)$m[1] : 30;

                    $existingStart = Carbon::parse($existingBooking->booking_date);
                    $existingEnd = $existingStart->copy()->addMinutes($existingDuration);

                    // Cek tabrakan waktu
                    return $requestedStart->lt($existingEnd) && $requestedEnd->gt($existingStart);
                });

            if ($conflict->isNotEmpty()) {
                // Jika ada bentrok, kembalikan error 422 (Validation Error)
                return response()->json([
                    'message' => 'Jadwal Bentrok! Port ini sedang digunakan pada jam tersebut. Silakan pilih jam lain.'
                ], 422);
            }

            // 4. JIKA AMAN, SIMPAN KE DATABASE
            Booking::create([
                'user_id'        => Auth::id(),
                'booking_number' => $validated['booking_number'],
                'station_name'   => $validated['station_name'],
                'location'       => $validated['location'] ?? '-',
                'port_type'      => $validated['port_type'] ?? 'Regular',
                'duration'       => $validated['duration'],
                'total_price'    => $validated['total_price'],
                'status'         => 'Selesai',
                'booking_date'   => $requestedStart, // Simpan waktu yang direquest user
            ]);

            // Return JSON sukses (untuk axios di frontend)
            return response()->json(['status' => 'success', 'message' => 'Booking Berhasil Disimpan!']);
            
        } catch (\Exception $e) {
            Log::error("Error Booking: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan sistem.'], 500);
        }
    }
}