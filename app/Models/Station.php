<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'address',
        'city',
        'lat',
        'lng',
        'type',
        'location_type',
        'price',
        'service_fee',
        
        // --- TAMBAHAN WAJIB UTK FITUR BUKA TUTUP ---
        'is_open', // <--- Ini kuncinya agar bisa di-update dari Host Dashboard
        // -------------------------------------------

        'status', // (Opsional: Tetap simpan string 'Tersedia'/'Penuh' jika perlu)
        'is_private',
        'chargers_detail',
        'photo',
    ];

    protected $casts = [
        'chargers_detail' => 'array', 
        
        // --- CASTING AGAR JADI TRUE/FALSE ---
        'is_open' => 'boolean', // <--- Agar di Vue nanti terbaca true/false, bukan 1/0
        // ------------------------------------

        'is_private' => 'boolean',
        'lat' => 'float',
        'lng' => 'float',
        'price' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookings()
    {
        // Hubungkan berdasarkan 'station_name' karena di tabel booking kita simpan nama stasiun
        return $this->hasMany(Booking::class, 'station_name', 'name');
    }
}