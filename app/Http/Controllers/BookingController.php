<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'station_name'   => 'required',
            'booking_time'   => 'required', // Bisa string ISO atau format lain
            'duration'       => 'required', // String "45 menit"
            'total_price'    => 'required|numeric',
            // Field optional
            'booking_number' => 'nullable',
            'location'       => 'nullable',
            'port_type'      => 'nullable',
        ]);

        try {
            // 2. Generate Nomor Booking (Backend Side)
            $realBookingNumber = 'BK-' . strtoupper(uniqid());

            // 3. Parsing Durasi (Ambil angka menit)
            preg_match('/(\d+)\s*(menit|Jam)/i', $request->duration, $matches);
            $durationValue = isset($matches[1]) ? (int)$matches[1] : 30;
            $unit = isset($matches[2]) ? strtolower($matches[2]) : 'menit';
            
            // Konversi ke menit jika satuannya Jam
            $durationMinutes = ($unit === 'jam') ? $durationValue * 60 : $durationValue;

            // 4. Parsing Waktu Mulai (PENTING: Timezone Handling)
            // Kita paksa anggap input user adalah Waktu Jakarta
            // Format input dari JS biasanya "YYYY-MM-DD HH:mm:ss"
            try {
                $requestedStart = Carbon::parse($request->booking_time, 'Asia/Jakarta');
            } catch (\Exception $e) {
                 return response()->json(['status' => 'error', 'message' => 'Format waktu tidak valid.'], 400);
            }

            $requestedEnd = $requestedStart->copy()->addMinutes($durationMinutes);

            // 5. Cek Bentrok Jadwal (Conflict Detection)
            // Logika: (StartA < EndB) AND (EndA > StartB)
            $conflict = Booking::where('station_name', $request->station_name)
                ->where('port_type', $request->port_type ?? 'Regular')
                ->where('status', '!=', 'Batal') // Abaikan yg batal
                ->get()
                ->filter(function ($existingBooking) use ($requestedStart, $requestedEnd) {
                    
                    // Parse durasi booking lama
                    preg_match('/(\d+)\s*(menit|Jam)/i', $existingBooking->duration, $m);
                    $exVal = isset($m[1]) ? (int)$m[1] : 30;
                    $exUnit = isset($m[2]) ? strtolower($m[2]) : 'menit';
                    $exDuration = ($exUnit === 'jam') ? $exVal * 60 : $exVal;

                    // Waktu booking lama (DB biasanya UTC, kita convert ke Jakarta utk perbandingan)
                    $existingStart = Carbon::parse($existingBooking->booking_date)->timezone('Asia/Jakarta');
                    $existingEnd = $existingStart->copy()->addMinutes($exDuration);

                    // Cek irisan waktu
                    return $requestedStart->lt($existingEnd) && $requestedEnd->gt($existingStart);
                });

            if ($conflict->isNotEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jadwal Bentrok! Port ini sedang digunakan pada jam tersebut. Silakan pilih jam lain.'
                ], 422); // 422 Unprocessable Entity
            }

            // 6. Simpan ke Database
            $booking = Booking::create([
                'user_id'        => Auth::id(),
                'booking_number' => $realBookingNumber,
                'station_name'   => $request->station_name,
                'location'       => $request->location ?? '-',
                'port_type'      => $request->port_type ?? 'Regular',
                'duration'       => $request->duration,
                'total_price'    => $request->total_price,
                'status'         => 'Selesai',
                'booking_date'   => $requestedStart, // Disimpan sebagai UTC oleh Laravel
            ]);

            // 7. Return Sukses
            return response()->json([
                'status' => 'success', 
                'message' => 'Booking Berhasil!',
                'data' => $booking
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error Booking: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }
}