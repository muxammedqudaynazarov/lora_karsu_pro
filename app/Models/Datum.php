<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function device(): HasOne
    {
        return $this->hasOne(Device::class, 'id', 'device_id');
    }
}
