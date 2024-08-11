<?php

namespace App\Http\Controllers;

use App\Models\DataMaterial;
use App\Models\MaterialKeluar;
use App\Models\StokMaterial;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;

class MaterialKeluarController extends Controller
{
    public function index()
    {
        return view('page.admin.materialKeluar.index');
    }

    public function dataTable(Request $request)
    {
        $totalFilteredRecord = $totalDataRecord = $draw_val = "";
        $columns_list = array(
            0 => 'waktu',
            1 => 'data_material_id',
            2 => 'jumlah',
            3 => 'satuan',
            4 => 'id',
        );

        $totalDataRecord = MaterialKeluar::count();
        $totalFilteredRecord = $totalDataRecord;

        $limit_val = $request->input('length');
        $start_val = $request->input('start');
        $order_val = $columns_list[$request->input('order.0.column')];
        $dir_val = $request->input('order.0.dir');

        if (empty($request->input('search.value'))) {
            $material_keluar_data = MaterialKeluar::offset($start_val)
                ->limit($limit_val)
                ->orderBy($order_val, $dir_val)
                ->get();
        } else {
            $search_text = $request->input('search.value');

            $material_keluar_data = MaterialKeluar::where('waktu', 'LIKE', "%{$search_text}%")
                ->orWhereHas('dataMaterial', function($query) use ($search_text) {
                    $query->where('nama_material', 'LIKE', "%{$search_text}%")
                          ->orWhere('kode_material', 'LIKE', "%{$search_text}%");
                })
                ->offset($start_val)
                ->limit($limit_val)
                ->orderBy($order_val, $dir_val)
                ->get();

            $totalFilteredRecord = MaterialKeluar::where('waktu', 'LIKE', "%{$search_text}%")
                ->orWhereHas('dataMaterial', function($query) use ($search_text) {
                    $query->where('nama_material', 'LIKE', "%{$search_text}%")
                          ->orWhere('kode_material', 'LIKE', "%{$search_text}%");
                })
                ->count();
        }

        $data_val = array();
        if (!empty($material_keluar_data)) {
            foreach ($material_keluar_data as $material_keluar) {
                $url = route('materialKeluar.edit', ['id' => $material_keluar->id]);
                $urlHapus = route('materialKeluar.delete', $material_keluar->id);
                $materialKeluarNestedData['waktu'] = $material_keluar->waktu;
                $materialKeluarNestedData['nama_material'] = $material_keluar->dataMaterial->nama_material;
                $materialKeluarNestedData['kode_material'] = $material_keluar->dataMaterial->kode_material;
                $materialKeluarNestedData['jumlah'] = $material_keluar->jumlah;
                $materialKeluarNestedData['satuan'] = $material_keluar->satuan;
                $materialKeluarNestedData['options'] = "<a href='$url'><i class='fas fa-edit fa-lg'></i></a> 
                    <a style='border: none; background-color:transparent;' class='hapusData' data-id='$material_keluar->id' data-url='$urlHapus'><i class='fas fa-trash fa-lg text-danger'></i></a>";
                $data_val[] = $materialKeluarNestedData;
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

    public function tambahMaterialKeluar(Request $request)
    {
        if ($request->isMethod('post')) {
            $this->validate($request, [
                'waktu' => 'required|date',
                'nama_material' => 'required|exists:data_materials,id',
                'jumlah' => 'required|integer',
                'satuan' => 'required|string|max:9999',
            ]);

            MaterialKeluar::create([
                'waktu' => $request->waktu,
                'data_material_id' => $request->nama_material,
                'jumlah' => $request->jumlah,
                'satuan' => $request->satuan,
            ]);
            $StokMaterial=StokMaterial::where('data_material_id',$request->nama_material)->first();
            if($StokMaterial->stok >= $request->jumlah) {
                $stok= $StokMaterial->stok;
                $stokBaru= $stok - $request->jumlah;
                $maksimumstok= $StokMaterial->maksimum_stok;
                if ($maksimumstok>= $stokBaru) {
                    $status= 'Tidak Overstock';
                }else {
                    $status= 'Overstock';
                } 
                StokMaterial::where('data_material_id',$request->nama_material)->update([
                    'stok'=>$stokBaru,
                    'status'=>$status
                ]);
               
        }
            
            return redirect()->route('materialKeluar.add')->with('status', 'Data telah tersimpan di database');
        }
        $dataMaterials = DataMaterial::all();
        return view('page.admin.materialKeluar.addMaterialKeluar', compact('dataMaterials'));
    }

    public function ubahMaterialKeluar($id, Request $request)
    {
        $material_keluar = MaterialKeluar::findOrFail($id);
        if ($request->isMethod('post')) {
            $this->validate($request, [
                'waktu' => 'required|date',
                'nama_material' => 'required|exists:data_materials,id',
                'kode_material' => 'required|exists:data_materials,id',
                'jumlah' => 'required|integer',
                'satuan' => 'required|string|max:9999',
            ]);

            $material_keluar->update([
                'waktu' => $request->waktu,
                'data_material_id' => $request->nama_material,
                'jumlah' => $request->jumlah,
                'satuan' => $request->satuan,
            ]);

            return redirect()->route('materialKeluar.edit', ['id' => $material_keluar->id])->with('status', 'Data telah tersimpan di database');
        }
        $dataMaterials = DataMaterial::all();
        return view('page.admin.materialKeluar.ubahMaterialKeluar', [
            'material_keluar' => $material_keluar,
            'dataMaterials' => $dataMaterials,
        ]);
    }

    public function hapusMaterialKeluar($id)
    {
        $material_keluar = MaterialKeluar::findOrFail($id);
        $material_keluar->delete($id);
        return response()->json([
            'msg' => 'Data yang dipilih telah dihapus'
        ]);
    }

    public function downloadPdf() {
        $material = MaterialKeluar::with('dataMaterial')->get();

        $pdf = FacadePdf::loadView('page.admin.materialKeluar.dataMaterialkeluarPdf', ['material' => $material]);
        return $pdf->download('data-material-keluar-pdf');
    }
}
