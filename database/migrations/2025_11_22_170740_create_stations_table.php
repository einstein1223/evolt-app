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
            $table->string('name');
            
            $table->string('city')->nullable(); 
            $table->string('address')->nullable(); 
            $table->string('operational_hours')->default('24/7'); 
            
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            
            $table->decimal('price', 10, 2)->default(50000);
            $table->decimal('service_fee', 10, 2)->default(5000);
            
            $table->string('status')->default('Aktif');
            
            // JSON for chargers detail
            $table->json('chargers_detail')->nullable(); 

            // --- NEW COLUMNS FOR HOST FEATURE ---
            // These were missing causing the seeder error
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('is_private')->default(false); 
            $table->string('contact_person')->nullable(); 
            // ------------------------------------

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};