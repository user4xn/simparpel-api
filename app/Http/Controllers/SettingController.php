<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppSetting;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    //

    public function fetchAllSetting()
    {
        $data = AppSetting::all()->toArray();

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => "Successfully get data",
            'data' => $data
        ], 200);
    } // end func


    public function updateAllSetting(Request $request)
    {
        $data = $request->all();
        // start transaction
        DB::beginTransaction();

        try {
            foreach ($data as $key => $value):
                // update tiap setting
                AppSetting::where('name', strtolower($key))->update(['value' => strtolower($value)]);
            endforeach;

            // commit jika sukses
            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => "Successfully update data",
                'data' => $data
            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => $th->getMessage(),
                'data' => ''
            ], 400);
        } // end try
    } // end func


    public function fetchLiteSetting(Request $request)
    {
        // ambil setting dengan label 'device'
        $appSetting = AppSetting::select('name', 'value')->where('label', 'device')
            ->get();

        $data = [];

        foreach ($appSetting as $key => $value):
            $data[$value['name']] = $value['value'];
        endforeach;

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => "Successfully get data",
            'data' => $data
        ], 200);
    } // end func

    public function resetAllSetting(Request $request)
    {
        // start transaction
        DB::beginTransaction();

        try {
            // update tiap setting sesuai kolom default_value
            AppSetting::query()->update([
                'value' => DB::raw('default_value')
            ]);

            // commit jika sukses
            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => "Successfully update data",
                'data' => []
            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => $th->getMessage(),
                'data' => ''
            ], 400);
        } // end try
    }
}