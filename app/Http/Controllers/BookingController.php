<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Station;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Essential for Transactions
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Menyimpan Data Booking (Logic Transaksi Aman)
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            // Sebaiknya kirim station_id dari frontend, tapi jika hanya kirim nama, kita cari ID-nya
            'station_name'   => 'required|exists:stations,name', 
            'booking_time'   => 'required|date', 
            'duration'       => 'required', 
            'total_price'    => 'required|numeric',
            'location'       => 'nullable',
            'port_type'      => 'nullable',
        ]);

        // Mulai Transaksi Database (Atomic Lock)
        return DB::transaction(function () use ($request) {
            try {
                // 2. Parsing Waktu & Durasi
                $requestedStart = Carbon::parse($request->booking_time)->timezone('Asia/Jakarta');
                
                // Parsing Durasi (Handle "30 menit" atau "1 Jam")
                $durationMinutes = 30; // Default
                if (is_numeric($request->duration)) {
                    $durationMinutes = (int) $request->duration;
                } else {
                    if (stripos($request->duration, 'Jam') !== false) {
                        $durationMinutes = (int)$request->duration * 60;
                    } else {
                        $durationMinutes = (int)$request->duration;
                    }
                }
                
                $requestedEnd = $requestedStart->copy()->addMinutes($durationMinutes);

                // 3. Cari Station ID (Penting untuk relasi)
                $station = Station::where('name', $request->station_name)->firstOrFail();

                // 4. CEK BENTROK (RACE CONDITION PROOF)
                // Kita gunakan lockForUpdate() agar tidak ada user lain yg bisa baca/tulis saat pengecekan ini
                $conflict = Booking::where('station_id', $station->id) // Pakai ID, lebih akurat daripada nama
                    ->where('port_type', $request->port_type ?? 'Regular')
                    ->where('status', '!=', 'Batal')
                    ->where(function ($query) use ($requestedStart, $requestedEnd) {
                        // Logic Overlap:
                        // (Start Baru < End Lama) AND (End Baru > Start Lama)
                        $query->where('booking_date', '<', $requestedEnd)
                              ->where(DB::raw("DATE_ADD(booking_date, INTERVAL duration MINUTE)"), '>', $requestedStart);
                    })
                    ->lockForUpdate() // <--- KUNCI DATABASE
                    ->exists();

                if ($conflict) {
                    // Lempar error agar ditangkap catch block dan transaksi dibatalkan
                    throw new \Exception('Jadwal Bentrok! Slot waktu ini baru saja diambil orang lain.');
                }

                // 5. Generate Nomor Booking
                $realBookingNumber = 'BK-' . strtoupper(uniqid());

                // 6. Simpan Booking
                $booking = Booking::create([
                    'user_id'        => Auth::id(),
                    'booking_code'   => $realBookingNumber, // Pastikan kolom di DB 'booking_code' atau 'booking_number'
                    'station_id'     => $station->id,       // Simpan ID stasiun
                    'station_name'   => $station->name,     // Simpan nama juga untuk histori
                    'location'       => $station->address ?? $request->location,
                    'port_type'      => $request->port_type ?? 'Regular',
                    'duration'       => $durationMinutes, 
                    'total_price'    => $request->total_price,
                    'status'         => 'Selesai', // Atau 'Pending' jika ada payment gateway
                    'booking_date'   => $requestedStart,
                ]);

                // 7. Response Sukses
                return response()->json([
                    'status' => 'success', 
                    'message' => 'Booking Berhasil!',
                    'data' => [
                        'booking_number' => $booking->booking_code,
                        'station' => $booking->station_name,
                        'price' => $booking->total_price
                    ]
                ]);

            } catch (\Exception $e) {
                // Rollback otomatis terjadi karena kita pakai DB::transaction
                Log::error("Error Booking: " . $e->getMessage());
                
                // Return Error code 422 (Unprocessable Entity) untuk dibaca frontend
                return response()->json([
                    'status' => 'error', 
                    'message' => $e->getMessage() // Pesan error "Jadwal Bentrok" akan muncul disini
                ], 422);
            }
        });
    }
}