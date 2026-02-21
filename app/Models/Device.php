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
        'status',
    ];

    public function datum()
    {
        return $this->hasOne(Datum::class)->latestOfMany();
    }
}
