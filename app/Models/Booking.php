<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'station_name',
        'location',
        'booking_code', // Penting
        'booking_date',
        'duration',
        'port_type',
        'total_price',
        'status',
    ];

    protected $casts = [
        'booking_date' => 'datetime',
        'total_price' => 'integer',
        'duration' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_name', 'name');
    }
}