<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Station;

class StationSeeder extends Seeder
{
    public function run(): void
    {
        // Cari user Host Jaecoo Batam
        $host = User::where('username', 'Jaecoo Batam')->first();

        if ($host) {
            Station::create([
                'user_id' => $host->id,
                'name' => 'Jaecoo Batam Charging Station',
                'address' => 'Jalan Taman Baloi, Showroom Jaecoo Batam',
                'city' => 'Batam',
                'lat' => 1.1275, // Disesuaikan dengan cast float di model
                'lng' => 104.0142,
                'type' => 'Fast Charging', // Sesuaikan dengan tipe yang kamu gunakan (misal: AC/DC)
                'location_type' => 'Showroom',
                'price' => 50000, // Cast integer
                'service_fee' => 5000,
                'is_open' => true, // Cast boolean
                'status' => 'Tersedia', 
                'is_private' => false, // Cast boolean
                // Menggunakan array langsung karena model sudah nge-cast 'chargers_detail' => 'array'
                'chargers_detail' => [
                    [
                        'tipe' => 'CCS2',
                        'daya' => '50kW',
                        'status' => 'Tersedia'
                    ]
                ],
                'photo' => null, // Bisa diisi path foto nanti jika sudah ada
            ]);
            
            $this->command->info('Station Jaecoo Batam berhasil ditambahkan!');
        } else {
            $this->command->error('User Jaecoo Batam tidak ditemukan.');
        }
    }
}