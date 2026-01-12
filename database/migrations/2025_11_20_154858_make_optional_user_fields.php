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
            // PERBAIKAN: Gunakan 'nomor_telepon' bukan 'phone'
            // Pastikan kolom ini benar-benar ada sebelum diubah
            if (Schema::hasColumn('users', 'nomor_telepon')) {
                $table->string('nomor_telepon')->nullable()->change();
            }

            // Opsional: Jika nomor_plat juga ingin dibuat tidak wajib diisi
            if (Schema::hasColumn('users', 'nomor_plat')) {
                $table->string('nomor_plat')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kembalikan ke kondisi wajib isi (not null)
            if (Schema::hasColumn('users', 'nomor_telepon')) {
                // Catatan: Ini bisa error jika ada data kosong saat rollback
                $table->string('nomor_telepon')->nullable(false)->change();
            }
            if (Schema::hasColumn('users', 'nomor_plat')) {
                $table->string('nomor_plat')->nullable(false)->change();
            }
        });
    }
};