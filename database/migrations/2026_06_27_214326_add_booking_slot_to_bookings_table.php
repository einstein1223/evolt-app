<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Tambah kolom booking_slot setelah booking_date
            // Contoh value: "08:00", "13:00", "17:00"
            $table->string('booking_slot', 5)->nullable()->after('booking_date');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('booking_slot');
        });
    }
};