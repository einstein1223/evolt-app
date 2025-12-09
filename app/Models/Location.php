<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $table = 'locations';

    protected $fillable = [
        'name',
        'lat',
        'lng'
    ];

    // Relasi: Satu lokasi bisa punya banyak stasiun
    public function stations()
    {
        return $this->hasMany(Station::class);
    }
}