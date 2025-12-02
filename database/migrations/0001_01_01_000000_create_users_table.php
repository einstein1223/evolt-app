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
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('username'); // Pastikan ini username, BUKAN name
        $table->string('email')->unique();
        $table->string('nomor_plat')->nullable();     // Tambahan kolom
        $table->string('nomor_telepon')->nullable();  // Tambahan kolom
        $table->string('role')->default('user');      // Tambahan kolom
        
        // Kolom bawaan lainnya...
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
        $table->timestamps();
        
        // Kolom opsional lain dari fillable kamu (kalau mau dipakai)
        $table->string('gender')->nullable();
        $table->date('birthDate')->nullable();
        $table->string('idType')->nullable();
        $table->string('idNumber')->nullable();
        $table->string('city')->nullable();
    });

        // --- BATAS PERUBAHAN ---


        // Bagian ini standar dan tidak perlu diubah
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};