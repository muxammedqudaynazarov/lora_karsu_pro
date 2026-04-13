<?php

use App\Http\Controllers\DeviceController;
use App\Http\Controllers\LoraController;
use Illuminate\Support\Facades\Route;

Route::post('/lora', [LoraController::class, 'store']);
Route::get('/lora/get', [LoraController::class, 'index']);

Route::get('/devices', [DeviceController::class, 'devices']);
Route::get('/devices/{id}/data/{day}/get', [DeviceController::class, 'device_data']);
Route::get('/devices/now', [DeviceController::class, 'now']);
