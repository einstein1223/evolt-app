<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
   protected $fillable = [
        'username',
        // 'name' sudah dihapus
        'email',
        'password',
        'nomor_telepon',
        'role', 
        
        // --- DATA KENDARAAN ---
        'nomor_plat',
        'car_brand',
        'car_series',
        'car_type',
        
        // Data Opsional Lainnya
        'gender',
        'city',
        'birthDate',
        'idType',
        'idNumber',
        'battery_capacity', // Tambahkan ini
        'max_range',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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