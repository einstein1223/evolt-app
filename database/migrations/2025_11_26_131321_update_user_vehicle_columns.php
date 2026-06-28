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
        $table->string('nomor_plat')->nullable()->change();
        
        // Data Mobil
        $table->string('car_brand')->nullable(); 
        $table->string('car_series')->nullable(); 
        
        // Data Spesifikasi (PENTING untuk fitur baterai/jarak)
        $table->float('battery_capacity')->nullable(); 
        $table->integer('max_range')->nullable();
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
