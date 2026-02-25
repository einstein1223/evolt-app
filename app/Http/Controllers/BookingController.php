<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Station;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        // 1. Sanitasi Data
        $duration = (int) filter_var($request->duration, FILTER_SANITIZE_NUMBER_INT);
        $request->merge(['duration' => $duration]);

        // 2. Validasi
        $request->validate([
            'station_name'   => 'required|exists:stations,name',
            'booking_time'   => 'required|date',
            'duration'       => 'required|integer|min:10',
            'total_price'    => 'required|numeric',
            'port_type'      => 'nullable|string',
        ]);

        // 3. Transaksi DB
        DB::transaction(function () use ($request, $duration) { 
            // Ambil waktu dari request
            $requestedStart = Carbon::parse($request->booking_time)->timezone('Asia/Jakarta');
            $requestedEnd = $requestedStart->copy()->addMinutes($duration);
            
            // Cari Stasiun
            $station = Station::where('name', $request->station_name)->firstOrFail();

            // Cek Bentrok Jadwal (Concurrency Control)
            $conflict = Booking::where('station_id', $station->id)
                ->where('status', '!=', 'Batal')
                ->where(function ($query) use ($requestedStart, $requestedEnd) {
                    $query->where('booking_date', '<', $requestedEnd)
                          ->where('end_time', '>', $requestedStart); 
                })
                ->lockForUpdate() // Mencegah user lain memesan slot yang sama saat proses
                ->exists();

            if ($conflict) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'booking_time' => 'Maaf, slot waktu ini baru saja diambil orang lain.'
                ]);
            }

            // Simpan ke Database
            Booking::create([
                'user_id'        => Auth::id(),
                'plate_number'   => Auth::user()->nomor_plat ?? 'BP ----', 
                'station_id'     => $station->id,
                'station_name'   => $station->name,
                'booking_code'   => 'EV-' . strtoupper(uniqid()),
                'location'       => $station->location ?? $station->address,
                'booking_date'   => $requestedStart,
                'end_time'       => $requestedEnd,
                'duration'       => $duration,
                'total_price'    => $request->total_price,
                'port_type'      => $request->port_type ?? 'Regular',
                'status'         => 'Booked',
            ]);
        });

        return to_route('history')->with('message', 'Booking Berhasil!');
    }

    public function history()
    {
        $bookings = Booking::where('user_id', Auth::id())
            ->orderBy('booking_date', 'desc')
            ->get();

        return Inertia::render('user/History', [
            'bookings' => $bookings
        ]);
    }
}