<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    // Pastikan nama tabelnya benar (biasanya 'brands')
    protected $table = 'brands';
    
    // Izinkan kolom ini diisi
    protected $fillable = ['name'];
}