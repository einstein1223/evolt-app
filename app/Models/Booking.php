<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plate_number',
        'station_id',     // <--- Wajib ada
        'station_name',
        'booking_code',
        'location',
        'booking_date',
        'end_time',       // <--- Wajib ada (karena kita pakai di controller)
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

    // UPDATE RELASI JUGA (Saran Terbaik)
    // Sebaiknya relasi menggunakan ID, bukan Nama.
    // Karena Nama bisa diubah, tapi ID itu permanen.
    public function station()
    {
        // Default Laravel mencari 'station_id' di tabel bookings
        return $this->belongsTo(Station::class); 
    }
}