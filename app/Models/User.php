<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'nomor_telepon',
        'role',
        
        // --- DATA KENDARAAN (PASTIKAN INI ADA) ---
        'nomor_plat',
        'car_brand',
        'car_type',
        'car_series',
        // -----------------------------------------
        
        // Data Opsional Lainnya
        'gender',
        'city',
        'birthDate',
        'idType',
        'idNumber',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relasi ke Booking
    public function bookings()
    {
        return $this->hasMany(Booking::class)->latest();
    }
}