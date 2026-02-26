<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'deviceName',
        'devEUI',
        'location',
        'status',
    ];

    public function datum()
    {
        return $this->hasOne(Datum::class)->latestOfMany();
    }

    public function data()
    {
        return $this->hasMany(Datum::class, 'device_id', 'id');
    }
}
