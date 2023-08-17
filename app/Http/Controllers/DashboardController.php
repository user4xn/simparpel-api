<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ship;
use App\Models\ShipLocationLog;
use App\Models\ParkingLog;
use App\Models\Harbour;

class DashboardController extends Controller
{
    public function overview()
    {
        $ship = Ship::count();
        $shipUnamed = Ship::whereNull('name')->count();
        $harbour = Harbour::count();

        $fetch = [
            'total_harbour' => $harbour,
            'total_ship' => $ship,
            'total_unnamed_ship' => $shipUnamed,
        ];

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message'=> "Successfully get dashboard overview",
            'data' => $fetch
        ], 200);
    }
}
