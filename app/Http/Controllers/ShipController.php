<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\Ship;
use App\Models\ShipLocationLog;
use App\Models\ParkingLog;
use App\Models\HarbourGeofence as Geofence;

class ShipController extends Controller
{
    public function ships(Request $request)
    {
        $fetch = Ship::with('harbourDetail')->get()->map(function($item){
            return [
                'id' => $item->id,
                'name' => $item->name,
                'device_id' => $item->device_id,
                'firebase_token' => $item->firebase_token,
                'lat' => $item->lat,
                'long' => $item->long,
                'status' => $item->status,
                'harbour' => $item->status != 'idle' ? $item->harbourDetail->name : null,
            ];
        });   

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message'=> "Successfully get ships",
            'data' => $fetch
        ], 200);
    }

    public function shipStatus(Request $request, $id)
    {
        $ship = Ship::where('id', $id)
            ->with('harbourDetail')
            ->first();  

        $logLocation = ShipLocationLog::where('ship_id', $id)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get();
        
        $logParking = ParkingLog::where('ship_id', $id)
            ->orderBy('created_at', 'DESC')
            ->get();

        $fetch = [
            'ship_detail' => $ship,
            'location_log' => $logLocation,
            'parking_log' => $logParking,
        ];

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message'=> "Successfully get ships",
            'data' => $fetch
        ], 200);
    }

    public function nameShip(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $ship = Ship::where('id', $id)->update([
                'name' => $request->ship_name
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message'=> "Successfully name ships",
                'data' => $ship
            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message'=> $th->getMessage(),
                'data' => ''
            ], 400);
        }
    }

    public function upsertShip(Request $request)
    {
        DB::beginTransaction();
        try {
            $is_update = Ship::where('device_id', $request->device_id)->first();
            
            if($is_update) {
                $ship = $is_update;

                $sll = new ShipLocationLog();
                $sll->ship_id = $ship->id;
                $sll->lat = $request->lat;
                $sll->long = $request->long;
                $sll->save();

                $nearestHarbourid = $this->checkNearestHarbour($request->lat, $request->long);

                $harbourGeofence = Geofence::where('harbour_id', $nearestHarbourid)
                    ->select('long','lat')
                    ->get()->map(function($item){
                        return [
                            $item->lat, $item->long
                        ];
                    });

                if($this->statusCheck([$request->lat, $request->long], $harbourGeofence)){
                    $parkingLog = new ParkingLog();
                    $parkingLog->ship_id = $ship->id;
                    $parkingLog->harbour_id = $nearestHarbourid;
                    $parkingLog->ship_id = $ship->id;
                    $parkingLog->status = 'checkin';
                    $parkingLog->save();
                    
                    $status = 'checkin';
                } else {
                    $lastLogs = ParkingLog::where(['ship_id' => $ship->id, 'harbour_id' => $nearestHarbourid])
                        ->orderBy('created_at', 'DESC')
                        ->first();
                    
                    if($lastLogs && $lastLogs->status == 'checkin') {
                        $parkingLog = new ParkingLog();
                        $parkingLog->ship_id = $ship->id;
                        $parkingLog->harbour_id = $nearestHarbourid;
                        $parkingLog->ship_id = $ship->id;
                        $parkingLog->status = 'checkout';
                        $parkingLog->save();

                        $status = 'checkout';
                    } else {
                        $status = 'idle';
                    }
                    
                }
                
                Ship::where('id', $ship->id)->update([
                    'lat' => $request->lat,
                    'long' => $request->long,
                    'harbour_id' => $status != 'idle' ? $nearestHarbourid : null,
                    'status' => $status
                ]);

            } else {
                $ship = new Ship();
                $ship->device_id = $request->device_id;
                $ship->firebase_token = $request->firebase_token;
                $ship->lat = $request->lat;
                $ship->long = $request->long;
                $ship->save();

                $sll = new ShipLocationLog();
                $sll->ship_id = $ship->id;
                $sll->lat = $request->lat;
                $sll->long = $request->long;
                $sll->save();
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message'=> "Successfully set ship",
                'data' => $ship->id
            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message'=> $th->getMessage(),
                'data' => ''
            ], 400);
        }
    }

    function statusCheck($coord, $polygon) {
        $x = $coord[0];
        $y = $coord[1];
        
        $isInside = false;
        
        $numVertices = count($polygon);
        for ($i = 0, $j = $numVertices - 1; $i < $numVertices; $j = $i++) {
            $xi = $polygon[$i][0];
            $yi = $polygon[$i][1];
            $xj = $polygon[$j][0];
            $yj = $polygon[$j][1];
            
            $intersect = (($yi > $y) != ($yj > $y)) &&
                         ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);
            
            if ($intersect) {
                $isInside = !$isInside;
            }
        }
        
        return $isInside;
    }

    function checkNearestHarbour($lat, $long)
    {
        $targetLat = $lat;
        $targetLong = $long;

        $location_coordinates = Geofence::all()->toArray();

        $minDistance = PHP_INT_MAX;
        $nearestLocationId = null;

        foreach ($location_coordinates as $coordinate) {
            $lat = floatval($coordinate['lat']);
            $long = floatval($coordinate['long']);

            $distance = sqrt(pow($lat - $targetLat, 2) + pow($long - $targetLong, 2));

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearestLocationId = $coordinate['harbour_id'];
            }
        }

        return $nearestLocationId;
    }
}
