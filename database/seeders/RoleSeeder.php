<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun ADMIN
        User::create([
            'username' => 'Super Admin',
            'email' => 'admin@evolt.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'nomor_plat' => 'ADMIN-01',
            'nomor_telepon' => '08111111111',
        ]);

        // 2. Akun OPERATOR
        User::create([
            'username' => 'Petugas SPKLU',
            'email' => 'operator@evolt.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
            'nomor_plat' => 'OPR-01',
            'nomor_telepon' => '08222222222',
        ]);

        // 3. Akun MITRA HOST (BARU - Agar bisa test route Host)
        User::create([
            'username' => 'Mitra Tetangga',
            'email' => 'host@evolt.com',
            'password' => Hash::make('password'),
            'role' => 'host', // Pastikan ini sama dengan pengecekan di controller
            'nomor_plat' => 'HOST-01',
            'nomor_telepon' => '08333333333',
        ]);
        
        // 4. Akun USER CONTOH
        User::create([
            'username' => 'Pengguna EV',
            'email' => 'user@evolt.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'nomor_plat' => 'BP 1234 EV',
            'nomor_telepon' => '08555555555',
        ]);
    }
}