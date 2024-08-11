<?php

namespace App\Http\Controllers;

use App\Models\DataMaterial;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Illuminate\Http\Request;

class DataMaterialController extends Controller
{
    public function index()
    {

        return view('page.admin.dataMaterial.index');
    }

    public function dataTable(Request $request)
    {
        $totalFilteredRecord = $totalDataRecord = $draw_val = "";
        $columns_list = array(
            0 => 'id',
            1 => 'nama_material',
            2 => 'kode_material',
            3 => 'created_by',
            4 => 'created_at',
        );

        $totalDataRecord = DataMaterial::count();
        $totalFilteredRecord = $totalDataRecord;

        $limit_val = $request->input('length');
        $start_val = $request->input('start');
        $order_val = $columns_list[$request->input('order.0.column')];
        $dir_val = $request->input('order.0.dir');

        if (empty($request->input('search.value'))) {
            $material_data = DataMaterial::offset($start_val)
                ->limit($limit_val)
                ->orderBy($order_val, $dir_val)
                ->get();
        } else {
            $search_text = $request->input('search.value');

            $material_data = DataMaterial::where('nama_material', 'LIKE', "%{$search_text}%")
                ->orWhere('kode_material', 'LIKE', "%{$search_text}%")
                ->offset($start_val)
                ->limit($limit_val)
                ->orderBy($order_val, $dir_val)
                ->get();

            $totalFilteredRecord = DataMaterial::where('nama_material', 'LIKE', "%{$search_text}%")
                ->orWhere('kode_material', 'LIKE', "%{$search_text}%")
                ->count();
        }

        $data_val = array();
        if (!empty($material_data)) {
            foreach ($material_data as $material) {
                $created_by = $material->user->name;
                $waktu = date('d-m-Y', strtotime($material->created_at));

                $url = route('dataMaterial.edit', ['id' => $material->id]);
                $urlHapus = route('dataMaterial.delete', $material->id);
                $materialNestedData['nama_material'] = $material->nama_material;
                $materialNestedData['kode_material'] = $material->kode_material;
                $materialNestedData['created_by'] = $created_by;
                $materialNestedData['created_at'] = $waktu;

                if (auth()->user()->hasRole('admin')) {
                    $materialNestedData['options'] = "
                        <a href='$url'><i class='fas fa-edit fa-lg'></i></a>
                        <a style='border: none; background-color:transparent;' class='hapusData' data-id='$material->id' data-url='$urlHapus'><i class='fas fa-trash fa-lg text-danger'></i></a>
                    ";
                }

                $data_val[] = $materialNestedData;
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

    public function tambahMaterial(Request $request)
    {
        if ($request->isMethod('post')) {
            $this->validate($request, [
                'nama_material' => 'required|string|max:200|min:3',
                'kode_material' => 'required|string|max:100|unique:data_materials,kode_material',
            ]);

            DataMaterial::create([
                'nama_material' => $request->nama_material,
                'kode_material' => $request->kode_material,
                'created_by' => auth()->user()->id,
            ]);

            return redirect()->route('dataMaterial.add')->with('status', 'Data telah tersimpan di database');
        }
        return view('page.admin.dataMaterial.addMaterial');
    }

    public function ubahMaterial($id, Request $request)
    {
        $material = DataMaterial::findOrFail($id);
        if ($request->isMethod('post')) {
            $this->validate($request, [
                'nama_material' => 'required|string|max:200|min:3',
                'kode_material' => 'required|string|max:100|unique:data_materials,kode_material,' . $material->id,
            ]);

            $material->update([
                'nama_material' => $request->nama_material,
                'kode_material' => $request->kode_material,
            ]);

            return redirect()->route('dataMaterial.edit', ['id' => $material->id])->with('status', 'Data telah tersimpan di database');
        }
        return view('page.admin.dataMaterial.ubahMaterial', [
            'material' => $material
        ]);
    }

    public function hapusMaterial($id)
    {
        $material = DataMaterial::findOrFail($id);
        $material->delete($id);
        return response()->json([
            'msg' => 'Data yang dipilih telah dihapus'
        ]);
    }

    public function downloadPdf()
    {
        $material = DataMaterial::all();

        $pdf = FacadePdf::loadView('page.admin.dataMaterial.dataMaterialPdf', ['material' => $material]);
        return $pdf->download('data-material-pdf');
    }
}
