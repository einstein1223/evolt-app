<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
       return [
   // Ganti 'name' menjadi 'username'
            'username' => fake()->userName(), 
            
            // Tambahkan data untuk nomor telepon agar tidak error jika kolom ini wajib diisi
            'nomor_telepon' => fake()->numerify('08##########'), 
            
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            
            // Berdasarkan log error Anda, sepertinya Anda juga memiliki kolom 'role'
            'role' => 'user',
];

    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    
}
