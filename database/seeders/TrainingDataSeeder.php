<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrainingDataSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // D1: Pagi, AC, WD, Perumahan -> T
            ['waktu' => 'Pagi', 'konektor' => 'AC', 'hari' => 'WD', 'lokasi' => 'Perumahan', 'kepadatan' => 'T'],
            
            // D2: Siang, DC, WE, Mall -> P
            ['waktu' => 'Siang', 'konektor' => 'DC', 'hari' => 'WE', 'lokasi' => 'Mall', 'kepadatan' => 'P'],
            
            // D3: Sore, AC, WE, Mall -> P
            ['waktu' => 'Sore', 'konektor' => 'AC', 'hari' => 'WE', 'lokasi' => 'Mall', 'kepadatan' => 'P'],
            
            // D4: Pagi, DC, WD, Station Umum -> T
            ['waktu' => 'Pagi', 'konektor' => 'DC', 'hari' => 'WD', 'lokasi' => 'Station Umum', 'kepadatan' => 'T'],
            
            // D5: Siang, AC, WE, Perumahan -> T
            ['waktu' => 'Siang', 'konektor' => 'AC', 'hari' => 'WE', 'lokasi' => 'Perumahan', 'kepadatan' => 'T'],
            
            // D6: Sore, DC, WD, Station Umum -> P
            ['waktu' => 'Sore', 'konektor' => 'DC', 'hari' => 'WD', 'lokasi' => 'Station Umum', 'kepadatan' => 'P'],
            
            // D7: Siang, DC, WD, Mall -> P
            ['waktu' => 'Siang', 'konektor' => 'DC', 'hari' => 'WD', 'lokasi' => 'Mall', 'kepadatan' => 'P'],
            
            // D8: Pagi, AC, WE, Mall -> P
            ['waktu' => 'Pagi', 'konektor' => 'AC', 'hari' => 'WE', 'lokasi' => 'Mall', 'kepadatan' => 'P'],
            
            // D9: Sore, AC, WD, Perumahan -> T
            ['waktu' => 'Sore', 'konektor' => 'AC', 'hari' => 'WD', 'lokasi' => 'Perumahan', 'kepadatan' => 'T'],
            
            // D10: Siang, AC, WD, Station Umum -> T
            ['waktu' => 'Siang', 'konektor' => 'AC', 'hari' => 'WD', 'lokasi' => 'Station Umum', 'kepadatan' => 'T'],
        ];

        DB::table('training_data')->insert($data);
    }
}