<?php

namespace App\Http\Controllers;

use App\Models\DataMaterial;
use App\Models\MaterialMasuk;
use App\Models\StokMaterial;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;

class MaterialMasukController extends Controller
{
    public function index()
    {
        return view('page.admin.materialMasuk.index');
    }

    public function dataTable(Request $request)
    {
        $totalFilteredRecord = $totalDataRecord = $draw_val = "";
        $columns_list = array(
            0 => 'id',
            1 => 'waktu',
            2 => 'data_material_id',
            3 => 'jumlah',
            4 => 'satuan',
            5 => 'created_by',
        );

        $totalDataRecord = MaterialMasuk::count();
        $totalFilteredRecord = $totalDataRecord;

        $limit_val = $request->input('length');
        $start_val = $request->input('start');
        $order_val = $columns_list[$request->input('order.0.column')];
        $dir_val = $request->input('order.0.dir');

        if (empty($request->input('search.value'))) {
            $material_masuk_data = MaterialMasuk::offset($start_val)
                ->limit($limit_val)
                ->orderBy($order_val, $dir_val)
                ->get();
        } else {
            $search_text = $request->input('search.value');

            $material_masuk_data = MaterialMasuk::where('waktu', 'LIKE', "%{$search_text}%")
                ->orWhereHas('dataMaterial', function ($query) use ($search_text) {
                    $query->where('nama_material', 'LIKE', "%{$search_text}%")
                        ->orWhere('kode_material', 'LIKE', "%{$search_text}%");
                })
                ->offset($start_val)
                ->limit($limit_val)
                ->orderBy($order_val, $dir_val)
                ->get();

            $totalFilteredRecord = MaterialMasuk::where('waktu', 'LIKE', "%{$search_text}%")
                ->orWhereHas('dataMaterial', function ($query) use ($search_text) {
                    $query->where('nama_material', 'LIKE', "%{$search_text}%")
                        ->orWhere('kode_material', 'LIKE', "%{$search_text}%");
                })
                ->count();
        }

        $data_val = array();
        if (!empty($material_masuk_data)) {
            foreach ($material_masuk_data as $material_masuk) {
                $url = route('materialMasuk.edit', ['id' => $material_masuk->id]);
                $urlHapus = route('materialMasuk.delete', $material_masuk->id);
                $materialMasukNestedData['waktu'] = $material_masuk->waktu;
                $materialMasukNestedData['nama_material'] = $material_masuk->dataMaterial->nama_material;
                $materialMasukNestedData['kode_material'] = $material_masuk->dataMaterial->kode_material;
                $materialMasukNestedData['jumlah'] = $material_masuk->jumlah;
                $materialMasukNestedData['satuan'] = $material_masuk->satuan;
                $materialMasukNestedData['created_by'] = $material_masuk->user->name;
                if (auth()->user()->hasRole('admin')) {
                    $materialMasukNestedData['options'] = "<a href='$url'><i class='fas fa-edit fa-lg'></i></a>
                    <a style='border: none; background-color:transparent;' class='hapusData' data-id='$material_masuk->id' data-url='$urlHapus'><i class='fas fa-trash fa-lg text-danger'></i></a>";
                }
                $data_val[] = $materialMasukNestedData;
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

    public function tambahMaterialMasuk(Request $request)
    {
        if ($request->isMethod('post')) {
            $this->validate($request, [
                'waktu' => 'required|date',
                'nama_material' => 'required|exists:data_materials,id',
                'kode_material' => 'exists:data_materials,id',
                'jumlah' => 'required|integer',
                'satuan' => 'required|string|max:9999',
            ]);

            if ($request->jumlah <= 0) {
                return redirect()->route('materialMasuk.add')->with('error', 'Jumlah yang diinputkan harus lebih dari 0');
            }

            if (strtolower($request->satuan) != 'ton') {
                return redirect()->route('materialMasuk.add')->with('error', 'Satuan yang diinputkan harus ton');
            }

            MaterialMasuk::create([
                'waktu' => $request->waktu,
                'data_material_id' => $request->nama_material,
                'jumlah' => $request->jumlah,
                'satuan' => $request->satuan,
                'created_by' => auth()->user()->id,
            ]);

            $StokMaterial = StokMaterial::where('data_material_id', $request->nama_material)->first();
            if ($StokMaterial) {
                $stok = $StokMaterial->stok;
                $stokBaru = $stok + $request->jumlah;
                $maksimumstok = $StokMaterial->maksimum_stok;
                if ($maksimumstok >= $stokBaru) {
                    $status = 'Tidak Overstock';
                } else {
                    $status = 'Overstock';
                }
                StokMaterial::where('data_material_id', $request->nama_material)->update([
                    'stok' => $stokBaru,
                    'status' => $status
                ]);
            } else {
                StokMaterial::create([
                    'data_material_id' => $request->nama_material,
                    'stok' => $request->jumlah,
                    'maksimum_stok' => 20,
                ]);
            };
            return redirect()->route('materialMasuk.add')->with('status', 'Data telah tersimpan di database');
        }



        $dataMaterials = DataMaterial::all();
        return view('page.admin.materialMasuk.addMaterialMasuk', compact('dataMaterials'));
    }


    public function ubahMaterialMasuk($id, Request $request)
    {
        $material_masuk = MaterialMasuk::findOrFail($id);
        if ($request->isMethod('post')) {
            $this->validate($request, [
                'waktu' => 'required|date',
                'nama_material' => 'required|exists:data_materials,id',
                'kode_material' => 'required|exists:data_materials,id',
                'jumlah' => 'required|integer',
                'satuan' => 'required|string|max:50',
            ]);

            if ($request->jumlah <= 0) {
                return redirect()->route('materialMasuk.add')->with('error', 'Jumlah yang diinputkan harus lebih dari 0');
            }

            $old_stok = $material_masuk->jumlah;
            $material_masuk->update([
                'waktu' => $request->waktu,
                'data_material_id' => $request->nama_material,
                'jumlah' => $request->jumlah,
                'satuan' => $request->satuan,
            ]);

            $stok_material = StokMaterial::where('data_material_id', $material_masuk->data_material_id)->first();
            if ($stok_material) {
                $stok = $stok_material->stok;
                $stokBaru = $stok - $old_stok + $request->jumlah;
                $maksimumstok = $stok_material->maksimum_stok;
                if ($maksimumstok >= $stokBaru) {
                    $status = 'Tidak Overstock';
                } else {
                    $status = 'Overstock';
                }
                StokMaterial::where('data_material_id', $material_masuk->data_material_id)->update([
                    'stok' => $stokBaru,
                    'status' => $status
                ]);
            } else {
                return redirect()->route('materialMasuk.edit', ['id' => $material_masuk->id])->with('status', 'Data tidak ditemukan');
            }

            return redirect()->route('materialMasuk.edit', ['id' => $material_masuk->id])->with('status', 'Data telah tersimpan di database');
        }
        $dataMaterials = DataMaterial::all();
        return view('page.admin.materialMasuk.ubahMaterialMasuk', [
            'material_masuk' => $material_masuk,
            'dataMaterials' => $dataMaterials,
        ]);
    }

    public function hapusMaterialMasuk($id)
    {
        $material_masuk = MaterialMasuk::findOrFail($id);

        $stok_material = StokMaterial::where('data_material_id', $material_masuk->data_material_id)->first();
        if ($stok_material) {
            $stok = $stok_material->stok;
            $stokBaru = $stok - $material_masuk->jumlah;
            $maksimumstok = $stok_material->maksimum_stok;
            if ($maksimumstok >= $stokBaru) {
                $status = 'Tidak Overstock';
            } else {
                $status = 'Overstock';
            }
            StokMaterial::where('data_material_id', $material_masuk->data_material_id)->update([
                'stok' => $stokBaru,
                'status' => $status
            ]);
        }

        $material_masuk->delete($id);
        return response()->json([
            'msg' => 'Data yang dipilih telah dihapus'
        ]);
    }

    public function downloadPdf()
    {
        $material = MaterialMasuk::with('dataMaterial')->get();

        $pdf = FacadePdf::loadView('page.admin.materialMasuk.dataMaterialMasukPdf', ['material' => $material]);
        return $pdf->download('data-material-Masuk.pdf');
    }
}
