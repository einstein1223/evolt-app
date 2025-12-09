<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\CarType;
use App\Models\Location;
use App\Models\Station;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Merk & Tipe Mobil
        $brands = ['Hyundai', 'Wuling', 'Tesla', 'BYD', 'Kia', 'Toyota', 'Nissan', 'Lexus', 'BMW', 'Mercedes-Benz'];
        foreach ($brands as $b) Brand::firstOrCreate(['name' => $b]);

        $types = ['SUV', 'City Car', 'Hatchback', 'Sedan', 'MPV', 'Crossover', 'Coupe'];
        foreach ($types as $t) CarType::firstOrCreate(['name' => $t]);

        // 2. Lokasi (Area di Batam)
        $locations = [
            ['name' => 'Batam Center', 'lat' => 1.1301, 'lng' => 104.0529],
            ['name' => 'Nagoya', 'lat' => 1.1365, 'lng' => 104.0138],
            ['name' => 'Sekupang', 'lat' => 1.1147, 'lng' => 103.9325],
            ['name' => 'Batu Aji', 'lat' => 1.0354, 'lng' => 103.9637],
            ['name' => 'Lubuk Baja', 'lat' => 1.1335, 'lng' => 104.0114],
            ['name' => 'Tiban', 'lat' => 1.1095, 'lng' => 103.9893],
            ['name' => 'Kabil', 'lat' => 1.0812, 'lng' => 104.1072],
            ['name' => 'Nongsa', 'lat' => 1.1768, 'lng' => 104.0992],
            ['name' => 'Bengkong', 'lat' => 1.1356, 'lng' => 104.0309],
        ];

        foreach ($locations as $loc) {
            Location::firstOrCreate(['name' => $loc['name']], ['lat' => $loc['lat'], 'lng' => $loc['lng']]);

            // BUAT 5 STASIUN PER LOKASI (CAMPUR RESMI & TETANGGA)
            for ($i = 1; $i <= 5; $i++) {
                $latSpread = (rand(-30, 30) / 1000); 
                $lngSpread = (rand(-30, 30) / 1000);

                // --- LOGIKA PENTING: TETANGGA VS RESMI ---
                // Jika $i genap (2, 4), jadikan Charger Tetangga
                $isPrivate = ($i % 2 == 0); 
                
                $stationName = $isPrivate 
                    ? 'Rumah ' . fake()->firstName() . ' (Mitra Host)' // Contoh: Rumah Budi
                    : 'SPKLU ' . $loc['name'] . ' ' . chr(64 + $i);   // Contoh: SPKLU Batam Center A

                // Konfigurasi Charger
                $chargers = [];
                if ($isPrivate) {
                    // Tetangga biasanya cuma punya 1 port AC (Lambat)
                    $chargers[] = ['port' => 1, 'type' => 'Regular', 'power' => '7.4', 'kwh' => '7.4', 'connector' => 'Type 2'];
                    $price = rand(2500, 5000); // Lebih murah
                } else {
                    // Resmi punya Fast Charging
                    $chargers[] = ['port' => 1, 'type' => 'Fast', 'power' => '50', 'kwh' => '50', 'connector' => 'CCS2'];
                    $chargers[] = ['port' => 2, 'type' => 'Regular', 'power' => '22', 'kwh' => '22', 'connector' => 'Type 2'];
                    $price = rand(40000, 60000); // Lebih mahal
                }

                Station::create([
                    'name' => $stationName,
                    'city' => $loc['name'],
                    'address' => 'Jl. Kawasan ' . $loc['name'] . ' No. ' . rand(1, 99),
                    'operational_hours' => $isPrivate ? '08:00 - 20:00' : '24/7',
                    'lat' => $loc['lat'] + $latSpread,
                    'lng' => $loc['lng'] + $lngSpread,
                    'price' => $price,
                    'service_fee' => 2500,
                    'status' => 'Tersedia',
                    'chargers_detail' => $chargers,
                    
                    // KOLOM PENANDA TETANGGA
                    'is_private' => $isPrivate, 
                    'contact_person' => $isPrivate ? fake()->phoneNumber() : null
                ]);
            }
        }
    }
}
