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

// menu kapal
Route::get('/ship', 'App\Http\Controllers\ShipController@ships');
Route::get('/ship/{id}/status', 'App\Http\Controllers\ShipController@shipStatus');
Route::get('/ship/{device_id}/device', 'App\Http\Controllers\ShipController@shipStatusByDeviceId');
Route::post('/ship/upsert', 'App\Http\Controllers\ShipController@upsertShip');
Route::put('/ship/{id}/name', 'App\Http\Controllers\ShipController@nameShip');

// history kapal
Route::get('/ship/{id}/history/available', 'App\Http\Controllers\ShipHistoryController@available_date');
Route::get('/ship/{id}/history/{date}', 'App\Http\Controllers\ShipHistoryController@history_by_date');

// menu pelabuhan
Route::get('/harbour', 'App\Http\Controllers\HarbourController@harbours');
Route::post('/harbour/upsert', 'App\Http\Controllers\HarbourController@upsertHarbour');
Route::delete('/harbour/{id}/delete', 'App\Http\Controllers\HarbourController@deleteHarbour');

// menu setting
Route::get('/setting/fetch', 'App\Http\Controllers\SettingController@fetchAllSetting');
Route::post('/setting/update', 'App\Http\Controllers\SettingController@updateAllSetting');
Route::delete('/setting/reset', 'App\Http\Controllers\SettingController@resetAllSetting');