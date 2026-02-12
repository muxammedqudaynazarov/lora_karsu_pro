<?php

namespace App\Http\Controllers;

use App\Models\Lora;
use Illuminate\Http\Request;

class LoraController extends Controller
{
    public function store(Request $request)
    {
        $lora = new Lora();
        $lora->data = json_encode($request->all());
        $lora->save();
    }
}
