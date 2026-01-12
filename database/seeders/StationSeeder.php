<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Station;

class StationSeeder extends Seeder
{
    public function run()
    {
        // --- 1. BUAT USER (AKUN) ---
        
        // A. ADMIN (Untuk mengelola sistem)
        $admin = User::create([
            'username' => 'Super Admin',
            'email' => 'admin@evolt.com',
            'password' => Hash::make('password'), // Password: password
            'role' => 'admin',
        ]);

        // B. OPERATOR (Untuk SPKLU Umum/Perusahaan Besar)
        $operator = User::create([
            'username' => 'Operator evolt',
            'email' => 'operator@evolt.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
        ]);

        // C. HOST (Tetangga/Pribadi yang menyewakan charger)
        $host = User::create([
            'username' => 'Pak Budi (Tetangga)',
            'email' => 'host@evolt.com',
            'password' => Hash::make('password'),
            'role' => 'host',
        ]);

        // --- 2. BUAT STATION (SPKLU) ---

        // STATION 1: SPKLU Public (Punya Operator) - Lokasi: Station Umum
        Station::create([
            'user_id' => $operator->id,
            'name' => 'SPKLU Batam Centre Point',
            'address' => 'Jl. Engku Putri No. 1',
            'city' => 'Batam Center',
            'lat' => 1.1301, 
            'lng' => 104.0529,
            'type' => 'DC Fast', // Untuk Naive Bayes
            'location_type' => 'Station Umum', // PENTING: Untuk Naive Bayes
            'price' => 2400,
            'service_fee' => 5000,
            'status' => 'Available',
            'is_private' => false,
            // JSON Detail untuk Frontend
            'chargers_detail' => [
                ['type' => 'CCS2', 'power' => '50 kW'],
                ['type' => 'CHAdeMO', 'power' => '50 kW']
            ]
        ]);

        // STATION 2: SPKLU Mall (Punya Operator) - Lokasi: Mall
        Station::create([
            'user_id' => $operator->id,
            'name' => 'Grand Batam Mall Charging',
            'address' => 'Jl. Pembangunan, Batu Selicin',
            'city' => 'Lubuk Baja',
            'lat' => 1.1448, 
            'lng' => 104.0132,
            'type' => 'AC Standard',
            'location_type' => 'Mall', // PENTING: Untuk Naive Bayes
            'price' => 3000,
            'service_fee' => 7000,
            'status' => 'Available',
            'is_private' => false,
            'chargers_detail' => [
                ['type' => 'Type 2', 'power' => '22 kW'],
                ['type' => 'Type 2', 'power' => '22 kW']
            ]
        ]);

        // STATION 3: E-Volt Tetangga (Punya Host) - Lokasi: Perumahan
        Station::create([
            'user_id' => $host->id,
            'name' => 'Garasi Pak Budi',
            'address' => 'Perumahan Sukajadi Blok A',
            'city' => 'Sukajadi',
            'lat' => 1.1167, 
            'lng' => 104.0333,
            'type' => 'AC Standard',
            'location_type' => 'Perumahan', // PENTING: Untuk Naive Bayes
            'price' => 15000, // Harga paket per jam/sesi
            'service_fee' => 2000,
            'status' => 'Available',
            'is_private' => true, // Ini Private/Tetangga
            'chargers_detail' => [
                ['type' => 'Type 2 (Home)', 'power' => '7 kW']
            ]
        ]);

        // STATION 4: Station Padat (Simulasi Busy) - Lokasi: Mall
        Station::create([
            'user_id' => $operator->id,
            'name' => 'Nagoya Hill Charging',
            'address' => 'Jl. Teuku Umar',
            'city' => 'Nagoya',
            'lat' => 1.1458, 
            'lng' => 104.0089,
            'type' => 'DC Fast',
            'location_type' => 'Mall', 
            'price' => 2500,
            'service_fee' => 5000,
            'status' => 'Busy', // Status Busy
            'is_private' => false,
            'chargers_detail' => [
                ['type' => 'CCS2', 'power' => '100 kW']
            ]
        ]);
    }
}