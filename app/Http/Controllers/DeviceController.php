<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function show($devID)
    {
        $device = Device::with(['data' => function ($query) {
            $query->orderBy('id', 'desc')->get();
        }])->where('devEUI', $devID)->firstOrFail();

        $filteredData = $device->data->groupBy(function ($item) {
            $time = \Carbon\Carbon::parse($item->created_at);
            $minute = floor($time->minute / 15) * 15;
            return $time->format('Y-m-d H:') . str_pad($minute, 2, '0', STR_PAD_LEFT);
        })->map(function ($group) {
            return $group->first();
        })->take(50)->reverse()->values();
        $device->setRelation('data', $filteredData);
        return view('device.show', compact(['device']));
    }
}
