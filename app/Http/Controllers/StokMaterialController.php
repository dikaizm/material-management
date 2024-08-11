<?php

namespace App\Http\Controllers;

use App\Models\StokMaterial;
use App\Models\DataMaterial;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;

class StokMaterialController extends Controller
{
    public function index()
    {
        return view('page.admin.stokMaterial.index');
    }

    public function dataTable(Request $request)
    {
        $totalFilteredRecord = $totalDataRecord = $draw_val = "";
        $columns_list = array(
            0 => 'data_material_id',
            1 => 'stok',
            2 => 'maksimum_stok',
            3 => 'status',
            4 => 'id',
        );

        $totalDataRecord = StokMaterial::count();
        $totalFilteredRecord = $totalDataRecord;

        $limit_val = $request->input('length');
        $start_val = $request->input('start');
        $order_val = $columns_list[$request->input('order.0.column')];
        $dir_val = $request->input('order.0.dir');

        if (empty($request->input('search.value'))) {
            $stok_material_data = StokMaterial::offset($start_val)
                ->limit($limit_val)
                ->orderBy($order_val, $dir_val)
                ->get();
        } else {
            $search_text = $request->input('search.value');

            $stok_material_data = StokMaterial::whereHas('dataMaterial', function($query) use ($search_text) {
                    $query->where('nama_material', 'LIKE', "%{$search_text}%")
                          ->orWhere('kode_material', 'LIKE', "%{$search_text}%");
                })
                ->offset($start_val)
                ->limit($limit_val)
                ->orderBy($order_val, $dir_val)
                ->get();

            $totalFilteredRecord = StokMaterial::whereHas('dataMaterial', function($query) use ($search_text) {
                    $query->where('nama_material', 'LIKE', "%{$search_text}%")
                          ->orWhere('kode_material', 'LIKE', "%{$search_text}%");
                })
                ->count();
        }

        $data_val = array();
        if (!empty($stok_material_data)) {
            foreach ($stok_material_data as $stok_material) {
                $url = route('stokMaterial.edit', ['id' => $stok_material->id]);
                $urlHapus = route('stokMaterial.delete', $stok_material->id);
                $stokMaterialNestedData['nama_material'] = $stok_material->dataMaterial->nama_material;
                $stokMaterialNestedData['kode_material'] = $stok_material->dataMaterial->kode_material;
                $stokMaterialNestedData['stok'] = $stok_material->stok;
                $stokMaterialNestedData['maksimum_stok'] = $stok_material->maksimum_stok;
                $stokMaterialNestedData['status'] = $stok_material->status;
                $stokMaterialNestedData['options'] = "<a href='$url'><i class='fas fa-edit fa-lg'></i></a> 
                    <a style='border: none; background-color:transparent;' class='hapusData' data-id='$stok_material->id' data-url='$urlHapus'><i class='fas fa-trash fa-lg text-danger'></i></a>";
                $data_val[] = $stokMaterialNestedData;
            }
        }
        $draw_val = $request->input('draw');
        $get_json_data = array(
            "draw" => intval($draw_val),
            "recordsTotal" => intval($totalDataRecord),
            "recordsFiltered" => intval($totalFilteredRecord),
            "data" => $data_val
        );

        echo json_encode($get_json_data);
    }

    public function tambahStokMaterial(Request $request)
    {
        if ($request->isMethod('post')) {
            $this->validate($request, [
                'nama_material' => 'required|exists:data_materials,id',
                'stok' => 'required|integer',
                'maksimum_stok' => 'required|integer',
                'status' => 'required|in:Tidak Overstock,Overstock',
            ]);

            StokMaterial::create([
                'data_material_id' => $request->nama_material,
                'stok' => $request->stok,
                'maksimum_stok' => $request->maksimum_stok,
                'status' => $request->status,
            ]);

            return redirect()->route('stokMaterial.add')->with('status', 'Data telah tersimpan di database');
        }
        $dataMaterials = DataMaterial::all();
        return view('page.admin.stokMaterial.addStokMaterial', compact('dataMaterials'));
    }

    public function ubahStokMaterial($id, Request $request)
    {
        $stok_material = StokMaterial::findOrFail($id);
        if ($request->isMethod('post')) {
            $this->validate($request, [
                'nama_material' => 'required|exists:data_materials,id',
                'stok' => 'required|integer',
                'maksimum_stok' => 'required|integer',
            
            ]);

            if ($request->stok <= $request->maksimum_stok){
                $status = 'Tidak Overstock';
            }else{
                $status = 'Overstock';
            }

            
            $stok_material->update([
                'data_material_id' => $request->nama_material,
                'stok' => $request->stok,
                'maksimum_stok' => $request->maksimum_stok,
                'status' => $status,
            ]);

            return redirect()->route('stokMaterial.edit', ['id' => $stok_material->id])->with('status', 'Data telah tersimpan di database');
        }
        $dataMaterials = DataMaterial::all();
        return view('page.admin.stokMaterial.ubahStokMaterial', [
            'stok_material' => $stok_material,
            'dataMaterials' => $dataMaterials,
        ]);
    }

    public function hapusStokMaterial($id)
    {
        $stok_material = StokMaterial::findOrFail($id);
        $stok_material->delete($id);
        return response()->json([
            'msg' => 'Data yang dipilih telah dihapus'
        ]);
    }

    public function downloadPdf() {
        $material = StokMaterial::with('dataMaterial')->get();

        $pdf = FacadePdf::loadView('page.admin.stokMaterial.stokMaterialPdf', ['material' => $material]);
        return $pdf->download('stok-material-pdf');
    }
}
