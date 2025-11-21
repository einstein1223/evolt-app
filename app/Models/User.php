<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // <-- 1. INI DI-AKTIFKAN
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable implements MustVerifyEmail // <-- 2. INI DITAMBAHKAN
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
   protected $fillable = [
    'username',
    'email',
    'nomor_plat',
    'nomor_telepon',
    'password',
    'role',
    'gender',
    'birthDate',
    'idType',
    'idNumber',
    'city',
    'phone',
    'role',
];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
    public function bookings()
    {
        return $this->hasMany(Booking::class)->latest(); // Ambil data terbaru
    }
}
