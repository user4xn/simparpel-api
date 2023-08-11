<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LocationArea as Area;
use App\Models\LocationCoordinate as AreaCoordinate;
use DB;

class LocationAreaController extends Controller
{
    public function areas(Request $request)
    {
        $fetch = Area::with('coordinates')->get();
        
        $data = [
            'status' => 'success',
            'code' => 200,
            'message'=> "Successfully get areas",
            'data' => $fetch
        ];

        return response()->json($data, 200, $headers ?? []);
    }

    public function upsertArea(Request $request)
    {
        DB::beginTransaction();
        try {
            if($request->area_id != null) {
                $area = Area::where('id', $request->area_id)->update([
                    'name' => $request->name
                ]);
                
                $areaCoordinate = AreaCoordinate::where('location_id', $request->area_id)->delete();
                
                foreach ($request->coordinates as $point) {
                    $newAreaCoordniate = new AreaCoordinate();
                    $newAreaCoordniate->location_id = $request->area_id;
                    $newAreaCoordniate->lat = $point['lat'];
                    $newAreaCoordniate->long = $point['long'];
                    $newAreaCoordniate->save();
                }
            } else {
                $area = new Area();
                $area->name = $request->name;
                $area->save();

                foreach ($request->coordinates as $point) {
                    $newAreaCoordniate = new AreaCoordinate();
                    $newAreaCoordniate->location_id = $area->id;
                    $newAreaCoordniate->lat = $point['lat'];
                    $newAreaCoordniate->long = $point['long'];
                    $newAreaCoordniate->save();
                }
            }

            $data = [
                'status' => 'success',
                'code' => 200,
                'message'=> "Successfully set area",
                'data' => $area
            ];

            DB::commit();
            return response()->json($data, 200, $headers ?? []);
        } catch (\Throwable $th) {
            DB::rollback();
            return $th->getMessage();
        }
    }
}
