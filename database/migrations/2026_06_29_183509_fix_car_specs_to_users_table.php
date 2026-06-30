<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'car_type'))
                $table->string('car_type')->nullable();

            if (!Schema::hasColumn('users', 'battery_capacity'))
                $table->float('battery_capacity')->nullable();

            if (!Schema::hasColumn('users', 'max_range'))
                $table->float('max_range')->nullable();

            if (!Schema::hasColumn('users', 'charge_port_type'))
                $table->string('charge_port_type')->nullable();

            if (!Schema::hasColumn('users', 'max_charge_speed'))
                $table->float('max_charge_speed')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'car_type', 'battery_capacity',
                'max_range', 'charge_port_type', 'max_charge_speed',
            ]);
        });
    }
};