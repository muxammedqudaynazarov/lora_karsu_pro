<?php

use App\Http\Controllers\DeviceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('inform');
})->name('main');

Route::resource('data', DeviceController::class)->only(['show', 'edit']);
