<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Carbon\Carbon;
use App\Models\DataMaterial;
use App\Models\MaterialKeluar;
use App\Models\MaterialMasuk;
use App\Models\StokMaterialRecord;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/chart-data', function (Request $request) {
    $timezone = 'Asia/Jakarta';

    if ($request->has('year')) {
        $current_year = $request->input('year');
    } else {
        $current_year = Carbon::now($timezone)->format('Y');
    }

    if ($request->has('month')) {
        $current_month = $request->input('month');
    } else {
        $current_month = Carbon::now($timezone)->format('m');
    }

    $material_ins = MaterialMasuk::whereMonth('waktu', $current_month)->whereYear('waktu', $current_year)->get();
    $material_outs = MaterialKeluar::whereMonth('waktu', $current_month)->whereYear('waktu', $current_year)->get();
    $material_stock_records = StokMaterialRecord::whereMonth('waktu', $current_month)->whereYear('waktu', $current_year)->get();

    // Date as key
    $mapped_material_in_data = [];
    $mapped_material_out_data = [];
    $mapped_material_stock_data = [];

    foreach ($material_ins as $material_in) {
        $date = Carbon::parse($material_in->waktu)->format('Y-m-d');
        $material_code = $material_in->dataMaterial()->first()->kode_material;

        if (!isset($mapped_material_in_data[$date])) {
            $mapped_material_in_data[$date] = [];
        }

        if (!isset($mapped_material_in_data[$date][$material_code])) {
            $mapped_material_in_data[$date][$material_code] = 0;
        }

        $mapped_material_in_data[$date][$material_code] += $material_in->jumlah;
    }

    foreach ($material_outs as $material_out) {
        $date = Carbon::parse($material_out->waktu)->format('Y-m-d');
        $material_code = $material_out->dataMaterial()->first()->kode_material;

        if (!isset($mapped_material_out_data[$date])) {
            $mapped_material_out_data[$date] = [];
        }

        if (!isset($mapped_material_out_data[$date][$material_code])) {
            $mapped_material_out_data[$date][$material_code] = 0;
        }

        $mapped_material_out_data[$date][$material_code] += $material_out->jumlah;
    }

    foreach ($material_stock_records as $material_stock_record) {
        $date = Carbon::parse($material_stock_record->waktu)->format('Y-m-d');
        $material_code = $material_stock_record->dataMaterial()->first()->kode_material;

        if (!isset($mapped_material_stock_data[$date])) {
            $mapped_material_stock_data[$date] = [];
        }

        if (!isset($mapped_material_stock_data[$date][$material_code])) {
            $mapped_material_stock_data[$date][$material_code] = 0;
        }

        $mapped_material_stock_data[$date][$material_code] += $material_stock_record->stok;
    }

    return response()->json([
        'material_ins' => $mapped_material_in_data,
        'material_outs' => $mapped_material_out_data,
        'material_stock' => $mapped_material_stock_data,
    ]);
});
