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
    Schema::create('training_data', function (Blueprint $table) {
        $table->id();
        $table->string('waktu');     // Pagi, Siang, Sore
        $table->string('konektor');  // AC, DC
        $table->string('hari');      // WD (Weekday), WE (Weekend)
        $table->string('lokasi');    // Perumahan, Mall, Station Umum
        $table->string('kepadatan'); // T (Tidak Padat), P (Padat)
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_data');
    }
};
