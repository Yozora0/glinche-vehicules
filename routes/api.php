<?php

use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/vehicles', [VehicleController::class, 'index'])->name('api.vehicles.index');
Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show'])
    ->whereNumber('vehicle')
    ->name('api.vehicles.show');
Route::get('/brands', [VehicleController::class, 'brands'])->name('api.brands.index');

Route::post('/vehicles/sync', [VehicleController::class, 'sync'])
    ->middleware('throttle:5,1')
    ->name('api.vehicles.sync');
