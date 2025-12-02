<?php

namespace App\Http\Controllers;

use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class StationController extends Controller
{
    /**
     * Menyimpan Stasiun Baru ke Database
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location_id' => 'required|exists:locations,id', // Pastikan lokasi ada di tabel locations
            'address' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'price' => 'required|numeric',
            'power' => 'required|string', // e.g. "50 kW"
            'type' => 'required|string', // e.g. "Fast"
        ]);

        // 2. Simpan ke Database
        Station::create([
            'name' => $validated['name'],
            'location_id' => $validated['location_id'],
            'address' => $validated['address'],
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
            'price' => $validated['price'],
            'service_fee' => 5000, // Default fee
            'status' => 'Tersedia', // Default status
            // Karena kolom 'chargers' dan 'power' belum ada di tabel asli (kita simulasi di map),
            // untuk sementara data ini disimpan tapi tidak masuk DB kecuali kamu tambah kolomnya.
            // Jika ingin disimpan, kamu harus add column 'power' di migration stations.
        ]);

        return Redirect::back()->with('success', 'Stasiun berhasil ditambahkan!');
    }
    
    /**
     * Menghapus Stasiun
     */
    public function destroy($id)
    {
        Station::findOrFail($id)->delete();
        return Redirect::back()->with('success', 'Stasiun berhasil dihapus.');
    }
}