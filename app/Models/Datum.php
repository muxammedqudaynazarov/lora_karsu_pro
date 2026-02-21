<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Datum extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'temperature',
        'moisture',
        'electricity',
        'data',
    ];
}
