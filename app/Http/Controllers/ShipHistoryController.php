<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\Ship;
use App\Models\ShipLocationLog;
use App\Models\ParkingLog;
use App\Models\Harbour;
use App\Models\HarbourGeofence as Geofence;
use Log;

class ShipHistoryController extends Controller
{
    public function history_by_date(Request $request, $id, $date)
    {
        $logs = ShipLocationLog::select( 'lat', 'long')->where('ship_id', intval($id))
            ->where('on_ground', 0)
            ->whereRaw('DATE(created_at) = ?', [date('Y-m-d', strtotime($date))])
            ->groupBy('lat', 'long')
            ->get()->toArray();

        $fetch = ['id' => $id, 'date' => $date, 'history' => array_reverse($logs)];
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => "Successfully get ships",
            'data' => $fetch
        ], 200);
    }

    public function available_date(Request $request, $id)
    {
        $dates = ShipLocationLog::select(DB::raw('DATE(created_at) as date'))->where('ship_id', intval($id))
        ->groupBy(DB::raw('DATE(created_at)'))
            ->get();

        $available = [];

        foreach ($dates as $key => $value) {
            $available[] = $value->date;
        }

        $fetch = ['id' => $id, 'dates' => $available];
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => "Successfully get ships",
            'data' => $fetch
        ], 200);
    }
}