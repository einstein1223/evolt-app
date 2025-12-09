<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarType extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'car_types';

    // Kolom yang boleh diisi
    protected $fillable = ['name'];
}