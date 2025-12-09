<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    use HasFactory;

    protected $table = 'stations';

    protected $fillable = [
        'name', 
        'city', 
        'address', 
        'operational_hours',
        'lat', 
        'lng', 
        'chargers_detail', 
        'status',
        'price', 
        'service_fee'
    ];

    protected $casts = [
        'chargers_detail' => 'array',
    ];

    // --- RELASI PENTING (TAMBAHKAN INI) ---
    public function bookings()
    {
        // Relasi ke tabel bookings
        // Parameter 2 ('station_name'): Nama kolom di tabel bookings
        // Parameter 3 ('name'): Nama kolom di tabel stations yang cocok
        return $this->hasMany(Booking::class, 'station_name', 'name');
    }
    
    // (Opsional) Jika nanti kamu mau pakai relasi Location lagi
    // public function location() { ... }
}