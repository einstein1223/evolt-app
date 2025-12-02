<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\User;
use App\Models\Station;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil semua user (kecuali admin/operator)
        $users = User::where('role', 'user')->get();
        $stations = Station::all();

        if($users->isEmpty() || $stations->isEmpty()) return;

        // Buat transaksi untuk setiap user
        foreach ($users as $user) {
            // Random 3-10 transaksi per user
            $numBookings = rand(3, 10);

            for ($i = 0; $i < $numBookings; $i++) {
                $station = $stations->random();
                $daysAgo = rand(0, 60); // Transaksi dalam 2 bulan terakhir
                $bookingDate = Carbon::now()->subDays($daysAgo);

                Booking::create([
                    'user_id' => $user->id,
                    'booking_number' => 'BK-' . strtoupper(uniqid()),
                    'station_name' => $station->name,
                    'location' => $station->city,
                    'port_type' => $station->chargers_detail[0]['type'] ?? 'Regular',
                    'duration' => rand(30, 120) . ' menit',
                    'total_price' => rand(50000, 250000),
                    'status' => 'Selesai',
                    'booking_date' => $bookingDate,
                    'created_at' => $bookingDate,
                    'updated_at' => $bookingDate,
                ]);
            }
        }
    }
}