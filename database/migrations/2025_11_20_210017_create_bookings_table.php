<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // HANYA BOLEH ADA SATU Schema::create DISINI
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel users
            // onDelete('cascade') artinya jika user dihapus, history bookingnya ikut hilang
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            
            $table->string('booking_number'); // Kode booking (BK1001)
            $table->string('station_name');   // Nama Station
            $table->string('location');       // Lokasi
            $table->string('port_type');      // Tipe Charger
            $table->string('duration');       // Durasi
            $table->decimal('total_price', 10, 2); // Total Harga
            $table->string('status')->default('Selesai'); // Status
            $table->dateTime('booking_date'); // Waktu Booking
            
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ini fungsi untuk menghapus tabel jika kamu melakukan rollback
        Schema::dropIfExists('bookings');
    }
};