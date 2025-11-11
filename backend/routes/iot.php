<?php

use App\Http\Controllers\Api\IoTDeviceController;
use Illuminate\Support\Facades\Route;

// IoT Device Routes - These routes are included within auth:sanctum middleware group in api.php
// Property IoT Devices
Route::get('/properties/{property}/iot-devices', [IoTDeviceController::class, 'propertyDevices']);

// Individual Device Control
Route::get('/iot-devices/{device}', [IoTDeviceController::class, 'show']);
Route::post('/iot-devices/{device}/command', [IoTDeviceController::class, 'sendCommand']);
Route::get('/iot-devices/{device}/history', [IoTDeviceController::class, 'deviceHistory']);
Route::get('/iot-devices/{device}/commands', [IoTDeviceController::class, 'commandHistory']);

// Thermostat Control
Route::post('/iot-devices/{device}/thermostat', [IoTDeviceController::class, 'controlThermostat']);

// Light Control
Route::post('/iot-devices/{device}/light', [IoTDeviceController::class, 'controlLight']);

// Camera Access
Route::get('/iot-devices/{device}/camera/stream', [IoTDeviceController::class, 'getCameraStream']);


