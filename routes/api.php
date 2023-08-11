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

Route::get('/marker', 'App\Http\Controllers\CoordinateController@coordinates');
Route::get('/marker/{id}/status', 'App\Http\Controllers\CoordinateController@coordinateStatus');
Route::post('/marker/upsert', 'App\Http\Controllers\CoordinateController@upsertCoordinate');

Route::get('/location-area', 'App\Http\Controllers\LocationAreaController@areas');
Route::post('/location-area/upsert', 'App\Http\Controllers\LocationAreaController@upsertArea');
