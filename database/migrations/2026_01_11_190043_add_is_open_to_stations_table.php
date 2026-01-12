<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('stations', function (Blueprint $table) {
        $table->boolean('is_open')->default(false)->after('status'); 
        // default false (tutup) atau true (buka) terserah kamu
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stations', function (Blueprint $table) {
            //
        });
    }
};
