<?php

namespace App\Http\Controllers;

use App\Models\Datum;
use App\Models\Device;
use Illuminate\Http\Request;

class LoraController extends Controller
{
    public function index()
    {
        $devices = Device::with('datum')->orderBy('deviceName')->where('status', '1')->get();

        $devices->each(function ($device) {
            if ($device->datum) {
                // 1. Keraksiz qatorlarni yashirish
                $device->datum->makeHidden(['temperature', 'moisture', 'electricity']);

                // 2. String ko'rinishidagi JSON ma'lumotni Array (massiv) ga o'girish
                if (is_string($device->datum->data)) {
                    $device->datum->data = json_decode($device->datum->data, true);
                }
            }
        });

        return response()->json($devices);
    }

    public function store(Request $request)
    {
        $device = Device::firstOrCreate(['devEUI' => $request->devEUI], ['deviceName' => $request->deviceName]);
        if ($device) {
            $datum = Datum::create([
                'device_id' => $device->id,
                'temperature' => $request->temperature,
                'moisture' => $request->moisture,
                'electricity' => $request->electricity,
                'data' => json_encode($request->all()),
            ]);
            return response()->json([
                'status' => 'success',
                'data' => $datum
            ]);
        }
        return response()->json(['status' => false]);
    }
}
