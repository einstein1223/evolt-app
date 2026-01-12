<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            // Seeder Data Latih Naive Bayes (Yang kita buat sebelumnya)
            TrainingDataSeeder::class, 
            
            // Seeder Akun & Station (Yang baru dibuat)
            StationSeeder::class, 
        ]);
    }
}