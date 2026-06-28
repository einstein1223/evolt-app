<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class HostSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'username' => 'Jaecoo Batam', // <-- Ganti 'name' menjadi 'username'
            'email' => 'admin@jaecoobatam.com', // Sesuai dengan data yang kamu mau
            'password' => Hash::make('password123'), // Password default untuk login
            'role' => 'host',
            'city' => 'Batam', // Karena di tabel ada kolom city, bisa kita isi sekalian
        ]);
    }
}