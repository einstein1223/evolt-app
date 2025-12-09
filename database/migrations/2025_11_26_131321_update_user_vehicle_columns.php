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
    Schema::table('users', function (Blueprint $table) {
        // Ubah nomor_plat jadi boleh kosong dulu
        $table->string('nomor_plat')->nullable()->change();
        
        // Tambah kolom baru untuk detail mobil
        $table->string('car_brand')->nullable();  // Merk: Hyundai, Wuling
        $table->string('car_type')->nullable();   // Tipe: SUV, Sedan
        $table->string('car_series')->nullable(); // Series: Ioniq 5, Air EV
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('nomor_plat')->nullable(false)->change();
        $table->dropColumn(['car_brand', 'car_type', 'car_series']);
    });
}
};
