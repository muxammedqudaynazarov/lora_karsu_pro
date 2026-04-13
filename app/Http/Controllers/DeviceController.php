<?php

namespace App\Http\Controllers;

use App\Exports\DataExport;
use App\Models\Datum;
use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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

    public function edit($id, Request $request)
    {
        $startDate = null;
        $endDate = null;

        if ($request->has('today')) {
            $startDate = Carbon::today();
            $endDate = Carbon::now();
        } elseif ($request->has('yesterday')) {
            $startDate = Carbon::yesterday();
            $endDate = Carbon::yesterday()->endOfDay();
        } elseif ($request->has('last7days')) {
            $startDate = Carbon::today()->subDays(7);
            $endDate = Carbon::yesterday()->endOfDay();
        } elseif ($request->has('lastmonths')) {
            $startDate = Carbon::today()->subDays(30);
            $endDate = Carbon::yesterday()->endOfDay();
        } else {
            return redirect()->back();
        }
        $hasData = Datum::where('device_id', $id)->whereBetween('created_at', [$startDate, $endDate])->exists();
        if (!$hasData) {
            return redirect()->back()->with('error', 'Данные за указанные даты не найдены!');
        }
        $fileName = 'sensor_data_device_' . $id . '_' . date('dmYHis') . '.xlsx';
        return Excel::download(new DataExport($id, $startDate, $endDate), $fileName);
    }

    public function devices()
    {
        $devices = Device::select(['id', 'deviceName', 'devEUI', 'location'])->where('status', '1')->get();
        return $devices;
    }

    public function device_data($id, $day = 3)
    {
        $device = Device::where('id', $id)->where('status', '1')->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found!',
            ], 404);
        }
        $last30DaysData = $device->data()
            ->select(['id', 'data', 'created_at'])
            ->where('created_at', '>=', now()->subDays($day))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                if (is_string($item->data)) {
                    $item->data = json_decode($item->data, true);
                }
                $item->timestamp = Carbon::parse($item->created_at)->timestamp * 1000;
                unset($item->created_at);
                return $item;
            });
        return response()->json([
            'success' => true,
            'device' => $device->only(['id', 'deviceName', 'devEUI', 'location']),
            'data' => $last30DaysData
        ]);
    }

    public function now()
    {
        $devices = Device::select(['id', 'deviceName', 'devEUI', 'location'])->where('status', '1')->with('datum')->get();
        $formattedData = $devices->map(function ($device) {
            if (!$device->datum) {
                return null;
            }

            $dataArray = is_string($device->datum->data)
                ? json_decode($device->datum->data, true)
                : $device->datum->data;
            $timestamp = Carbon::parse($device->datum->created_at)->timestamp * 1000;
            return [
                'id' => $device->datum->id,
                'deviceName' => $device->deviceName,
                'devEUI' => $device->devEUI,
                'location' => $device->location,
                'data' => $dataArray,
                'timestamp' => $timestamp,
            ];
        })->filter();
        return response()->json($formattedData->values());
    }
}
