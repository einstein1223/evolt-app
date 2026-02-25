<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // database/migrations/xxxx_create_bookings_table.php
public function up(): void {
    Schema::dropIfExists('bookings');
    Schema::create('bookings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('plate_number')->nullable(); 
        $table->foreignId('station_id')->constrained('stations')->cascadeOnDelete();
        $table->string('booking_code')->unique();
        $table->string('station_name'); 
        $table->string('location')->nullable();
        $table->dateTime('booking_date');
        $table->dateTime('end_time')->nullable(); 
        $table->integer('duration'); 
        $table->string('port_type')->default('Regular');
        $table->decimal('total_price', 12, 2);
        $table->string('status')->default('Booked'); 
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};