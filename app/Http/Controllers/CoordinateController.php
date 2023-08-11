<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\Coordinate as Marker;
use App\Models\LocationCoordinate as AreaCoordinate;

class CoordinateController extends Controller
{
    public function coordinates(Request $request)
    {
        $fetch = Marker::all();
        
        $data = [
            'status' => 'success',
            'code' => 200,
            'message'=> "Successfully get coordinate",
            'data' => $fetch
        ];

        return response()->json($data, 200, $headers ?? []);
    }

    public function coordinateStatus(Request $request)
    {
        $fetch = Marker::where('id', $request->id)->first();
        
        $data = [
            'status' => 'success',
            'code' => 200,
            'message'=> "Successfully get coordinate",
            'data' => $fetch
        ];

        return response()->json($data, 200, $headers ?? []);
    }

    public function upsertCoordinate(Request $request)
    {
        DB::beginTransaction();
        try {
            if($request->marker_id != null) {
                $marker = Marker::where('id', $request->marker_id)->first();

                $area = AreaCoordinate::where('location_id', $marker->location_id)
                    ->select('long','lat')
                    ->get()->map(function($item){
                        return [
                            $item->lat, $item->long
                        ];
                    });
                
                if($this->statusCheck([$request->lat, $request->long], $area)){
                    $status = 'Inside';
                } else {
                    $status = 'Outside';
                }
                
                Marker::where('id', $request->marker_id)->update([
                    'lat' => $request->lat,
                    'long' => $request->long,
                    'status' => $status
                ]);

            } else {
                $marker = new Marker();
                $marker->lat = $request->lat;
                $marker->long = $request->long;
                $marker->location_id = $request->location_id;
                $marker->save();
            }

            $data = [
                'status' => 'success',
                'code' => 200,
                'message'=> "Successfully set coordinate",
                'data' => $marker->id
            ];

            DB::commit();
            return response()->json($data, 200, $headers ?? []);
        } catch (\Throwable $th) {
            DB::rollback();
            return $th->getMessage();
        }
    }

    function statusCheck($marker, $polygon) {
        $x = $marker[0];
        $y = $marker[1];
        
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
}
