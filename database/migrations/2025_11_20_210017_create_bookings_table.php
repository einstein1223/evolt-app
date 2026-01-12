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
        // Hapus tabel lama jika ada agar bersih
        Schema::dropIfExists('bookings');

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke User
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Kode Booking Unik (Contoh: BKG-ABCD)
            $table->string('booking_code')->unique()->nullable(); // Nullable dulu untuk jaga-jaga
            
            // Info Stasiun (Kita pakai Nama Stasiun sebagai penghubung relasi untuk Host)
            $table->string('station_name'); 
            $table->string('location')->nullable();
            
            // Detail Booking
            $table->dateTime('booking_date'); // Waktu mulai cas
            $table->integer('duration');      // Durasi dalam menit
            $table->string('port_type')->default('Regular');
            $table->integer('total_price');
            
            // Status: Menunggu, Selesai, Batal
            $table->string('status')->default('Menunggu');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};