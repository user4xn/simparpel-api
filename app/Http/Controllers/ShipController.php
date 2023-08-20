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

class ShipController extends Controller
{
    public function ships(Request $request)
    {
        $fetch = Ship::with('harbourDetail')->get()->map(function($item){
            return [
                'id' => $item->id,
                'name' => $item->name ?? null,
                'device_id' => $item->device_id,
                'firebase_token' => $item->firebase_token,
                'lat' => $item->lat,
                'long' => $item->long,
                'status' => $item->status,
                'on_ground' => $item->on_ground,
                'harbour' => in_array($item->status, ['checkin', 'checkout']) && $item->harbourDetail ? $item->harbourDetail->name : null,
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
            ->join('harbours', 'harbours.id', '=', 'parking_logs.harbour_id')
            ->select('parking_logs.*', 'harbours.name as harbour_name')
            ->orderBy('harbours.created_at', 'DESC')
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

    public function shipStatusByDeviceId(Request $request, $device_id)
    {
        $ship = Ship::where('device_id', $device_id)
            ->select(
                "name",
                "device_id",
                "firebase_token",
                "long",
                "lat",
                "status",
                "harbour_id",
            )
            ->with('harbourDetail')
            ->first();  

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message'=> "Successfully get ships",
            'data' => $ship
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
                $sll->is_mocked = $request->is_mocked ? 1 : 0;
                $sll->save();

                $nearestHarbourid = $this->checkNearestHarbour($request->lat, $request->long);

                $harbourData = Harbour::where('id', $nearestHarbourid)->first();

                $harbourGeofence = Geofence::where('harbour_id', $nearestHarbourid)
                    ->select('long','lat')
                    ->get()->map(function($item){
                        return [
                            $item->lat, $item->long
                        ];
                    });

                if($this->statusCheck([$request->lat, $request->long], $harbourGeofence)){
                    $lastLogs = ParkingLog::where(['ship_id' => $ship->id, 'harbour_id' => $nearestHarbourid])
                        ->orderBy('created_at', 'DESC')
                        ->first();
                    
                    if($lastLogs && $lastLogs->status != 'checkin') {
                        $parkingLog = new ParkingLog();
                        $parkingLog->ship_id = $ship->id;
                        $parkingLog->harbour_id = $nearestHarbourid;
                        $parkingLog->status = 'checkin';
                        $parkingLog->save();

                        try{
                            $this->pushNotification([
                                'title' => 'SIMPARPEL - CHECK IN SUCCESS',
                                'body' => 'Berhasil CHECK-IN ('.ucwords($harbourData->name).') '.date('ymd-hi'),
                            ], [$ship->firebase_token]);
                        } catch (Exception $e){
                            Log::error('Push notification error: ' . $e->getMessage());
                        }
                    } else {
                        $isWater = $this->isWater($request->lat, $request->long);
                    }
                    
                    $status = 'checkin';
                } else {
                    $lastLogs = ParkingLog::where(['ship_id' => $ship->id, 'harbour_id' => $nearestHarbourid])
                        ->orderBy('created_at', 'DESC')
                        ->first();
                    
                    if($lastLogs && $lastLogs->status == 'checkin') {

                        if($ship->on_ground != 1) {
                            $isWater = $this->isWater($request->lat, $request->long);

                            if($isWater){
                                $parkingLog = new ParkingLog();
                                $parkingLog->ship_id = $ship->id;
                                $parkingLog->harbour_id = $nearestHarbourid;
                                $parkingLog->status = 'checkout';
                                $parkingLog->save();

                                try{
                                    $this->pushNotification([
                                        'title' => 'SIMPARPEL - CHECK OUT SUCCESS',
                                        'body' => 'Berhasil CHECK-OUT ('.ucwords($harbourData->name).') '.date('ymd-hi'),
                                    ], [$ship->firebase_token]);
                                } catch (Exception $e){
                                    Log::error('Push notification error: ' . $e->getMessage());
                                }
                            }

                            $status = $isWater ? 'checkout' : $ship->status;
                        } else {
                            $status = $ship->status;
                        }
                    } else {
                        $status = 'out of scoope';
                    }
                }
                
                Ship::where('id', $ship->id)->update([
                    'lat' => $request->lat,
                    'long' => $request->long,
                    'harbour_id' => $status != 'out of scoope' ? $nearestHarbourid : null,
                    'status' => $status,
                    'on_ground' => $isWater ? ($isWater == true ? false : true) : $ship->on_ground,
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

    public function pushNotification($data, $tokens) {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $headers = [
            'Content-Type: application/json',
            'Authorization: key='.env('FIREBASE_KEY'), // Replace with your FCM server key
        ];
    
        $postData = [
            'registration_ids' => $tokens,
            'notification' => $data,
        ];
    
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    
        $response = curl_exec($ch);
    
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['success' => false, 'error' => $error];
        }
    
        curl_close($ch);
        return ['success' => true, 'response' => json_decode($response, true)];
    }

    public function isWater($lat, $long)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://".env('RAPIDAPI_ISITWATER_HOST')."/?latitude=".$lat."&longitude=".$long,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "X-RapidAPI-Host: ".env('RAPIDAPI_ISITWATER_HOST'),
                "X-RapidAPI-Key: ".env('RAPIDAPI_KEY')
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if($err){
            Log::error('Check Water: ' . $err);
        } else {
            return json_decode($response)->water;
        }
    }
    
}
