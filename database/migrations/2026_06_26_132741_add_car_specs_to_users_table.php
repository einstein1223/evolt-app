<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'battery_capacity')) {
                $table->float('battery_capacity')->nullable(); // Dalam kWh
            }
            if (!Schema::hasColumn('users', 'max_range')) {
                $table->integer('max_range')->nullable();     // Dalam km
            }
            if (!Schema::hasColumn('users', 'car_type')) {
                $table->string('car_type', 50)->nullable();    // Tipe/Varian Mobil
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'battery_capacity')) {
                $table->dropColumn('battery_capacity');
            }
            if (Schema::hasColumn('users', 'max_range')) {
                $table->dropColumn('max_range');
            }
            if (Schema::hasColumn('users', 'car_type')) {
                $table->dropColumn('car_type');
            }
        });
    }
};
