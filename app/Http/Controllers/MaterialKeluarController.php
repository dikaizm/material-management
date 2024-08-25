<?php

namespace App\Http\Controllers;

use App\Models\DataMaterial;
use App\Models\MaterialKeluar;
use App\Models\MaterialMasuk;
use App\Models\StokMaterial;
use App\Models\StokMaterialRecord;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Carbon\Carbon;

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
            1 => 'id',
            2 => 'data_material_id',
            3 => 'jumlah',
            4 => 'satuan',
            5 => 'created_by',
        );

        $query = MaterialKeluar::query();

        if ($request->has('start_date') && $request->has('end_date')) {
            $start_date = $request->input('start_date');
            $end_date = $request->input('end_date');

            if ($start_date > $end_date) {
                return response()->json([
                    "error" => "Tanggal awal tidak boleh lebih besar dari tanggal akhir",
                    "draw" => intval($request->input('draw')),
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => []
                ]);
            }

            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween('waktu', [$start_date, $end_date]);
            }
        }

        $totalDataRecord = $query->count();
        $totalFilteredRecord = $totalDataRecord;

        $limit_val = $request->input('length');
        $start_val = $request->input('start');
        $order_val = $columns_list[$request->input('order.0.column')];
        $dir_val = $request->input('order.0.dir');

        if (empty($request->input('search.value'))) {
            $material_keluar_data = $query->offset($start_val)
                ->limit($limit_val)
                ->orderBy($order_val, $dir_val)
                ->get();
        } else {
            $search_text = $request->input('search.value');

            $material_keluar_data = $query->where('waktu', 'LIKE', "%{$search_text}%")
                ->orWhereHas('dataMaterial', function ($query) use ($search_text) {
                    $query->where('nama_material', 'LIKE', "%{$search_text}%")
                        ->orWhere('kode_material', 'LIKE', "%{$search_text}%");
                })
                ->offset($start_val)
                ->limit($limit_val)
                ->orderBy($order_val, $dir_val)
                ->get();

            $totalFilteredRecord = $query->where('waktu', 'LIKE', "%{$search_text}%")
                ->orWhereHas('dataMaterial', function ($query) use ($search_text) {
                    $query->where('nama_material', 'LIKE', "%{$search_text}%")
                        ->orWhere('kode_material', 'LIKE', "%{$search_text}%");
                })
                ->count();
        }

        $data_val = array();
        if (!empty($material_keluar_data)) {
            foreach ($material_keluar_data as $material_keluar) {
                $waktu = date('d-m-Y', strtotime($material_keluar->waktu));

                $url = route('materialKeluar.edit', ['id' => $material_keluar->id]);
                $urlHapus = route('materialKeluar.delete', $material_keluar->id);
                $materialKeluarNestedData['waktu'] = $waktu;
                $materialKeluarNestedData['nama_material'] = $material_keluar->dataMaterial->nama_material;
                $materialKeluarNestedData['kode_material'] = $material_keluar->dataMaterial->kode_material;
                $materialKeluarNestedData['jumlah'] = $material_keluar->jumlah;
                $materialKeluarNestedData['satuan'] = $material_keluar->satuan;
                $materialKeluarNestedData['created_by'] = $material_keluar->user->name;
                if (auth()->user()->hasRole('admin')) {
                    $materialKeluarNestedData['options'] = "
                        <a href='$url'><i class='fas fa-edit fa-lg'></i></a>
                        <a style='border: none; background-color:transparent;' class='hapusData' data-id='$material_keluar->id' data-url='$urlHapus'><i class='fas fa-trash fa-lg text-danger'></i></a>
                    ";
                }
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

            if (Carbon::parse($request->waktu)->toDateString() > Carbon::now('Asia/Jakarta')->toDateString()) {
                return redirect()->route('materialKeluar.add')->with('error', 'Waktu yang diinputkan tidak boleh lebih dari hari ini.');
            }

            if ($request->jumlah <= 0) {
                return redirect()->route('materialKeluar.add')->with('error', 'Jumlah yang diinputkan harus lebih dari 0');
            }

            if (strtolower($request->satuan) != 'ton') {
                return redirect()->route('materialKeluar.add')->with('error', 'Satuan yang diinputkan harus ton');
            }

            $stok_material = StokMaterial::where('data_material_id', $request->nama_material)->first();
            if (!$stok_material) {
                return redirect()->route('materialKeluar.add')->with('error', 'Data Material tidak ditemukan');
            }

            $material_in_sum = MaterialMasuk::where('data_material_id', $request->nama_material)->where('waktu', '<=', $request->waktu)->sum('jumlah');
            if ($material_in_sum < $request->jumlah) {
                return redirect()->route('materialKeluar.add')->with('error', "Stok pada tanggal {$request->waktu} tidak mencukupi");
            }

            $stok = $stok_material->stok;
            if ($stok >= $request->jumlah) {
                // get record with waktu and data_material_id
                $record = StokMaterialRecord::where('waktu', $request->waktu)->where('data_material_id', $request->nama_material)->orderBy('created_at', 'desc')->first();
                if ($record) {
                    $record->update([
                        'stok' => $record->stok - $request->jumlah
                    ]);
                } else {
                    $last_record_before = StokMaterialRecord::where('waktu', '<', $request->waktu)
                        ->where('data_material_id', $request->nama_material)->orderBy('waktu', 'desc')->first();

                    $record = StokMaterialRecord::create([
                        'waktu' => $request->waktu,
                        'data_material_id' => $request->nama_material,
                        'stok' => $last_record_before->stok - $request->jumlah,
                        'created_by' => auth()->user()->id
                    ]);
                }

                // get all records where waktu is greater than current record
                $records = StokMaterialRecord::where('waktu', '>', $request->waktu)->where('data_material_id', $request->nama_material)->get();
                if ($records) {
                    foreach ($records as $r) {
                        $r->update([
                            'stok' => $r->stok - $request->jumlah
                        ]);
                    }
                }

                MaterialKeluar::create([
                    'waktu' => $request->waktu,
                    'data_material_id' => $request->nama_material,
                    'jumlah' => $request->jumlah,
                    'satuan' => strtolower($request->satuan),
                    'created_by' => auth()->user()->id,
                    'record_id' => $record->id
                ]);

                $stokBaru = $stok - $request->jumlah;
                $maksimumstok = $stok_material->maksimum_stok;
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
                return redirect()->route('materialKeluar.add')->with('error', "Stok tidak mencukupi, stok saat ini: {$stok}");
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
                // 'nama_material' => 'required|exists:data_materials,id',
                // 'kode_material' => 'required|exists:data_materials,id',
                'jumlah' => 'required|integer',
                'satuan' => 'required|string|max:9999',
            ]);

            if (Carbon::parse($request->waktu)->toDateString() > Carbon::now('Asia/Jakarta')->toDateString()) {
                return redirect()->route('materialKeluar.edit', ['id' => $material_keluar->id])->with('error', 'Waktu yang diinputkan tidak boleh lebih dari hari ini.');
            }

            if ($request->jumlah <= 0) {
                return redirect()->route('materialKeluar.edit', ['id' => $material_keluar->id])->with('error', 'Jumlah yang diinputkan harus lebih dari 0');
            }

            if (strtolower($request->satuan) != 'ton') {
                return redirect()->route('materialKeluar.edit', ['id' => $material_keluar->id])->with('error', 'Satuan yang diinputkan harus ton');
            }

            $material_in_sum = MaterialMasuk::where('data_material_id', $material_keluar->data_material_id)->where('waktu', '<=', $request->waktu)->sum('jumlah');
            if ($material_in_sum < $request->jumlah) {
                return redirect()->route('materialKeluar.edit', ['id' => $material_keluar->id])->with('error', "Stok pada tanggal {$request->waktu} tidak mencukupi");
            }

            $stok_material = StokMaterial::where('data_material_id', $material_keluar->data_material_id)->first();
            if ($stok_material) {
                $old_stok = $material_keluar->jumlah;
                $material_keluar->update([
                    'waktu' => $request->waktu,
                    // 'data_material_id' => $request->nama_material,
                    'jumlah' => $request->jumlah,
                    'satuan' => strtolower($request->satuan),
                ]);

                $stok = $stok_material->stok;
                $stokBaru = $stok - ($request->jumlah - $old_stok);
                $maksimumstok = $stok_material->maksimum_stok;
                if ($maksimumstok >= $stokBaru) {
                    $status = 'Tidak Overstock';
                } else {
                    $status = 'Overstock';
                }
                StokMaterial::where('data_material_id', $request->nama_material)->update([
                    'stok' => $stokBaru,
                    'status' => $status
                ]);

                // Update stok record
                $record = StokMaterialRecord::where('id', $material_keluar->record_id)->first();
                if (!$record) {
                    return redirect()->route('materialKeluar.edit', ['id' => $material_keluar->id])->with('error', 'Data tidak ditemukan');
                }

                $record->update([
                    'stok' => $record->stok - ($request->jumlah - $old_stok)
                ]);

                // get all records where waktu is greater than the current record
                $records = StokMaterialRecord::where('waktu', '>', $request->waktu)
                    ->where('data_material_id', $record->data_material_id)
                    ->get();
                if ($records) {
                    foreach ($records as $r) {
                        $r->update([
                            'stok' => $r->stok - ($request->jumlah - $old_stok),
                        ]);
                    }
                }
            } else {
                return redirect()->route('materialKeluar.edit', ['id' => $material_keluar->id])->with('error', 'Data Material tidak ditemukan');
            }

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

        // Delete stok record
        $record = StokMaterialRecord::where('id', $material_keluar->record_id)->first();
        $material_in_records = MaterialKeluar::where('record_id', $record->id)->get();
        if ($material_in_records->count() == 1) {
            $record->delete();
        } else {
            $record->update([
                'stok' => $record->stok + $material_keluar->jumlah,
            ]);
        }

        // Update stok material untuk waktu lebih besar
        $records = StokMaterialRecord::where('waktu', '>', $record->waktu)
            ->where('data_material_id', $record->data_material_id)
            ->get();
        if ($records) {
            foreach ($records as $r) {
                $r->update([
                    'stok' => $r->stok + $material_keluar->jumlah
                ]);
            }
        }

        $stok_material = StokMaterial::where('data_material_id', $material_keluar->data_material_id)->first();
        if ($stok_material) {
            $stok = $stok_material->stok;
            $stokBaru = $stok + $material_keluar->jumlah;
            $maksimumstok = $stok_material->maksimum_stok;
            if ($maksimumstok >= $stokBaru) {
                $status = 'Tidak Overstock';
            } else {
                $status = 'Overstock';
            }
            StokMaterial::where('data_material_id', $material_keluar->data_material_id)->update([
                'stok' => $stokBaru,
                'status' => $status
            ]);
        }

        $material_keluar->delete($id);
        return response()->json([
            'msg' => 'Data yang dipilih telah dihapus'
        ]);
    }

    public function downloadPdf()
    {
        $material = MaterialKeluar::with('dataMaterial')->get();

        $pdf = FacadePdf::loadView('page.admin.materialKeluar.dataMaterialkeluarPdf', ['material' => $material]);
        return $pdf->download('data-material-keluar.pdf');
    }
}
