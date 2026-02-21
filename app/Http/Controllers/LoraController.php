<?php

namespace App\Http\Controllers;

use App\Models\Datum;
use App\Models\Device;
use Illuminate\Http\Request;

class LoraController extends Controller
{
    public function index()
    {
        $devices = Device::with('latestDatum')->where('status', '1')->get();
        return response()->json($devices);
    }

    public function store(Request $request)
    {
        $device = Device::firstOrCreate(['devEUI' => $request->devEUI], ['deviceName' => $request->deviceName]);
        if ($device) {
            $datum = Datum::create([
                'device_id' => $device->id,
                'temperature' => '1',
                'moisture' => '',
                'electricity' => '',
                'data' => json_decode($request->data),
            ]);
            return response()->json([
                'status' => 'success',
                'data' => $datum
            ]);
        }
        return response()->json(['status' => false]);
    }
}
