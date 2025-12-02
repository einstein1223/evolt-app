<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
   public function run(): void
    {
        $this->call([
            RoleSeeder::class,       // User & Admin
            MasterDataSeeder::class, // Stasiun & Mobil
            BookingSeeder::class,    // Transaksi (NEW)
        ]);
    }
}