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
        // Cek dulu sebelum menambahkan, hindari duplicate column error
        if (!Schema::hasColumn('users', 'battery_capacity')) {
            $table->float('battery_capacity')->nullable();
        }
        if (!Schema::hasColumn('users', 'charge_port_type')) {
            $table->string('charge_port_type')->nullable();
        }
        if (!Schema::hasColumn('users', 'max_charge_speed')) {
            $table->float('max_charge_speed')->nullable();
        }
        // tambahkan kolom lain yang ada di migration ini dengan pola yang sama
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
