<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Harbour as Harbour;
use App\Models\HarbourGeofence as Geofence;
use DB;

class HarbourController extends Controller
{
    public function harbours(Request $request)
    {
        $fetch = Harbour::with('coordinates')->get();
        
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message'=> "Successfully get harbours",
            'data' => $fetch
        ], 200);
    }

    public function upsertHarbour(Request $request)
    {
        DB::beginTransaction();
        try {
            if($request->area_id != null) {
                $harbour = Harbour::where('id', $request->harbour_id)->update([
                    'name' => $request->name
                ]);
                
                $harbourGeofence = Geofence::where('harbour_id', $request->harbour_id)->delete();
                
                foreach ($request->coordinates as $point) {
                    $newHarbourGeofence = new Geofence();
                    $newHarbourGeofence->harbour_id = $request->harbour_id;
                    $newHarbourGeofence->lat = $point['lat'];
                    $newHarbourGeofence->long = $point['long'];
                    $newHarbourGeofence->save();
                }
            } else {
                $harbour = new Harbour();
                $harbour->name = $request->name;
                $harbour->save();

                foreach ($request->coordinates as $point) {
                    $newHarbourGeofence = new Geofence();
                    $newHarbourGeofence->harbour_id = $harbour->id;
                    $newHarbourGeofence->lat = $point['lat'];
                    $newHarbourGeofence->long = $point['long'];
                    $newHarbourGeofence->save();
                }
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message'=> 'sucessfully set harbour',
                'data' => $harbour
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

    public function deleteHarbour(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $exec = Harbour::where('id', $id)->first();
            
            if(!$exec) {
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message'=> 'harbour id not found',
                    'data' => ''
                ], 400);
            }

            $harbourGeofence = Geofence::where('harbour_id', $request->harbour_id)->delete();

            $exec->delete();
            
            DB::commit();

            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message'=> 'harbour has been deleted',
                'data' => ''
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
}
