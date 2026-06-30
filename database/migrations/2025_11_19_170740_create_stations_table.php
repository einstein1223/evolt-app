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

            // ── Informasi Dasar ───────────────────────────────────────────
            $table->string('name');
            $table->text('address');
            $table->string('city')->default('');          // default kosong agar tidak error jika diisi manual
            $table->string('phone')->nullable();
            $table->string('photo')->nullable();

            // ── Koordinat (nullable agar form sederhana tetap bisa simpan) ─
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();

            // ── Kategori / Tipe ───────────────────────────────────────────
            $table->string('type')->nullable();           // Contoh: 'DC Fast', 'AC Standard'
            $table->string('location_type')->nullable();  // Contoh: 'Mall', 'Perumahan'

            // ── Harga ─────────────────────────────────────────────────────
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('service_fee', 10, 2)->default(0);

            // ── Status Operasional ────────────────────────────────────────
            $table->string('status')->default('Tutup');   // 'Tutup' | 'Tersedia' | 'Penuh'
            $table->boolean('is_open')->default(false);
            $table->boolean('is_private')->default(false);

            // ── Detail Charger (JSON) ─────────────────────────────────────
            $table->json('chargers_detail')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};