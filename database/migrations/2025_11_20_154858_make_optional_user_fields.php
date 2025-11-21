<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('gender')->nullable()->change();
            $table->string('birthDate')->nullable()->change();
            $table->string('idType')->nullable()->change();
            $table->string('idNumber')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('phone')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('gender')->nullable(false)->change();
            $table->string('birthDate')->nullable(false)->change();
            $table->string('idType')->nullable(false)->change();
            $table->string('idNumber')->nullable(false)->change();
            $table->string('city')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
        });
    }
};
