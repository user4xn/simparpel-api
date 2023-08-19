<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/dashboard/overview', 'App\Http\Controllers\DashboardController@overview');

Route::get('/ship', 'App\Http\Controllers\ShipController@ships');
Route::get('/ship/{id}/status', 'App\Http\Controllers\ShipController@shipStatus');
Route::get('/ship/{device_id}/device', 'App\Http\Controllers\ShipController@shipStatusByDeviceId');
Route::post('/ship/upsert', 'App\Http\Controllers\ShipController@upsertShip');
Route::put('/ship/{id}/name', 'App\Http\Controllers\ShipController@nameShip');

Route::get('/harbour', 'App\Http\Controllers\HarbourController@harbours');
Route::post('/harbour/upsert', 'App\Http\Controllers\HarbourController@upsertHarbour');
Route::delete('/harbour/{id}/delete', 'App\Http\Controllers\HarbourController@deleteHarbour');
