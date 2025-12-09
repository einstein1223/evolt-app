<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Station;
use App\Models\Brand;
use App\Models\CarType;
use App\Models\Location;
use Carbon\Carbon;

class MapResultController extends Controller
{
    public function index(Request $request)
    {
        $brands = Brand::all();
        $types = CarType::all();
        $locations = Location::all();
        $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');

        $stations = Station::with(['bookings' => function($query) use ($today) {
            $query->whereDate('booking_date', $today)->where('status', '!=', 'Batal'); 
        }])->get()->map(function ($s) {
            return [
                'id' => $s->id,
                'name' => $s->name,
                'location' => $s->city ?? 'Lokasi Tidak Diketahui', 
                'lat' => (float) $s->lat,
                'lng' => (float) $s->lng,
                'price' => (float) $s->price,
                'serviceFee' => (float) $s->service_fee,
                'status' => $s->status,
                // --- DATA BARU UNTUK TETANGGA ---
                'is_private' => $s->is_private ?? false, // Default false jika kolom belum ada
                'owner_name' => ($s->is_private ?? false) ? 'Mitra Host' : 'Official E-VOLT',
                // -------------------------------
                'isBookable' => $s->status === 'Tersedia',
                'chargers' => $s->chargers_detail ? collect($s->chargers_detail)->pluck('type')->toArray() : ['Fast'],
                'power' => $s->chargers_detail ? ($s->chargers_detail[0]['power'] . ' kW') : '50 kW',
                'bookingNumber' => 'BK-' . str_pad($s->id, 4, '0', STR_PAD_LEFT),
                'todayBookings' => $s->bookings->map(function($b) {
                    preg_match('/(\d+)\s*menit/', $b->duration, $matches);
                    $duration = isset($matches[1]) ? (int)$matches[1] : 30;
                    return [
                        'start_time' => Carbon::parse($b->booking_date)->timezone('Asia/Jakarta')->format('H:i'),
                        'duration_minutes' => $duration,
                        'port_type' => $b->port_type 
                    ];
                }),
            ];
        });

        return Inertia::render('user/MapResults', [
            'dbStations' => $stations,
            'dbBrands' => $brands,
            'dbTypes' => $types,
            'dbLocations' => $locations,
            'filters' => $request->all()
        ]);
    }
}