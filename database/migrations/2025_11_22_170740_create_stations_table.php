<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('stations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('name');
        $table->string('address');
        $table->string('city');
        $table->decimal('lat', 10, 8);
        $table->decimal('lng', 11, 8);
        
        // --- TAMBAHKAN DUA BARIS INI ---
        $table->string('type')->nullable();          // Contoh isi: 'DC Fast', 'AC Standard'
        $table->string('location_type')->nullable(); // Contoh isi: 'Mall', 'Perumahan'
        // -------------------------------

        $table->decimal('price', 10, 2)->default(0);
        $table->decimal('service_fee', 10, 2)->default(0);
        $table->string('status')->default('Available');
        $table->boolean('is_private')->default(false);
        $table->json('chargers_detail')->nullable();
        $table->string('photo')->nullable(); // Jika ada dari migration sebelumnya
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};