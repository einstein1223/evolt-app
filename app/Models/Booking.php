<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    // Izinkan kolom ini diisi
    protected $fillable = [
        'user_id',
        'booking_number',
        'station_name',
        'location',
        'port_type',
        'duration',
        'total_price',
        'status',
        'booking_date',
    ];

    // Relasi: Booking milik User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}