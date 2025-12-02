<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            // Relasi ke user (penting agar muncul di profile)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Data Transaksi
            $table->string('booking_number'); // Contoh: BK-123
            $table->string('station_name');   // Contoh: SPKLU Batam Center
            $table->string('location');       // Contoh: Batam Center
            $table->string('port_type');      // Contoh: Fast Charging
            $table->string('duration');       // Contoh: 60 menit
            $table->decimal('total_price', 15, 2); // Harga
            
            // Status & Waktu
            $table->string('status')->default('Selesai'); 
            $table->dateTime('booking_date');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};