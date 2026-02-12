<?php

use App\Http\Controllers\LoraController;
use Illuminate\Support\Facades\Route;

Route::post('/lora', [LoraController::class, 'store']);
Route::get('/lora/get', [LoraController::class, 'index']);
