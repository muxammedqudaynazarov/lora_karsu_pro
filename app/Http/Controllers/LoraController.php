<?php

namespace App\Http\Controllers;

use App\Models\Lora;
use Illuminate\Http\Request;

class LoraController extends Controller
{
    public function index()
    {
        $lora = Lora::orderBy('id', 'DESC')->first();
        return response()->json($lora);
    }

    public function store(Request $request)
    {
        $lora = new Lora();
        $lora->deviceName = $request->deviceName;
        $lora->devEUI = $request->devEUI;
        $lora->electricity = $request->electricity;
        $lora->moisture = $request->moisture;
        $lora->temperature = $request->temperature;
        $lora->data = json_encode($request->all());
        $lora->save();
    }
}
