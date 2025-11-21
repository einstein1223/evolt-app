<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        // Validasi data sederhana
        $request->validate([
            'booking_number' => 'required',
            'station_name' => 'required',
            'total_price' => 'required',
        ]);

        // Simpan ke database
        Booking::create([
            'user_id' => Auth::id(), // ID User yang login
            'booking_number' => $request->booking_number,
            'station_name' => $request->station_name,
            'location' => $request->location,
            'port_type' => $request->port_type,
            'duration' => $request->duration,
            'total_price' => $request->total_price,
            'status' => 'Selesai',
            'booking_date' => now(),
        ]);

        return redirect()->back()->with('message', 'Booking berhasil!');
    }
}