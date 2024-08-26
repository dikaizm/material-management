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
use Illuminate\Support\Facades\DB;

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
            0 => 'waktu',
            1 => 'id',
            2 => 'data_material_id',
            3 => 'jumlah',
            4 => 'satuan',
            5 => 'created_by',
        );

        $query = MaterialMasuk::query();

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
            $material_masuk_data = $query->offset($start_val)
                ->limit($limit_val)
                ->orderBy($order_val, $dir_val)
                ->get();
        } else {
            $search_text = $request->input('search.value');

            $material_masuk_data = $query->where('waktu', 'LIKE', "%{$search_text}%")
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
        if (!empty($material_masuk_data)) {
            foreach ($material_masuk_data as $material_masuk) {
                $waktu = date('d-m-Y', strtotime($material_masuk->waktu));

                $url = route('materialMasuk.edit', ['id' => $material_masuk->id]);
                $urlHapus = route('materialMasuk.delete', $material_masuk->id);
                $materialMasukNestedData['waktu'] = $waktu;
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

            if (Carbon::parse($request->waktu)->toDateString() > Carbon::now('Asia/Jakarta')->toDateString()) {
                return redirect()->route('materialMasuk.add')->with('error', 'Waktu yang diinputkan tidak boleh lebih dari hari ini.');
            }

            if ($request->jumlah <= 0) {
                return redirect()->route('materialMasuk.add')->with('error', 'Jumlah yang diinputkan harus lebih dari 0');
            }

            if (strtolower($request->satuan) != 'ton') {
                return redirect()->route('materialMasuk.add')->with('error', 'Satuan yang diinputkan harus ton');
            }

            // get record with waktu and data_material_id
            $record = StokMaterialRecord::where('waktu', $request->waktu)
                ->where('data_material_id', $request->nama_material)->orderBy('created_at', 'desc')->first();
            if ($record) {
                $record->update([
                    'stok' => $record->stok + $request->jumlah
                ]);
            } else {
                $last_record_before = StokMaterialRecord::where('waktu', '<', $request->waktu)
                    ->where('data_material_id', $request->nama_material)->orderBy('waktu', 'desc')->first();

                if (!$last_record_before) {
                    $last_record_stock = 0;
                } else {
                    $last_record_stock = $last_record_before->stok;
                }

                $record = StokMaterialRecord::create([
                    'data_material_id' => $request->nama_material,
                    'stok' => $last_record_stock + $request->jumlah,
                    'waktu' => $request->waktu,
                    'created_by' => auth()->user()->id,
                ]);
            }

            // get all records where waktu is greater than the current record
            $records = StokMaterialRecord::where('waktu', '>', $request->waktu)
                ->where('data_material_id', $request->nama_material)
                ->get();
            if ($records) {
                foreach ($records as $r) {
                    $r->update([
                        'stok' => $r->stok + $request->jumlah,
                    ]);
                }
            }

            MaterialMasuk::create([
                'waktu' => $request->waktu,
                'data_material_id' => $request->nama_material,
                'jumlah' => $request->jumlah,
                'satuan' => strtolower($request->satuan),
                'created_by' => auth()->user()->id,
                'record_id' => $record->id,
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
                'waktu' => 'required|date|before_or_equal:today',
                'jumlah' => 'required|integer|min:1',
                'satuan' => 'required|string|max:50|in:ton,TON',
            ]);

            try {
                DB::transaction(function () use ($request, $material_masuk) {
                    $this->validateEditMaterialKeluar($material_masuk, $request->jumlah);
                    $this->updateEditStokMaterial($material_masuk, $request->jumlah);
                    $this->updateMaterialMasuk($material_masuk, $request);
                    $this->updateStokMaterialRecords($material_masuk, $request->jumlah);
                });

                return redirect()->route('materialMasuk.edit', ['id' => $material_masuk->id])
                    ->with('status', 'Data telah tersimpan di database');
            } catch (\Exception $e) {
                return redirect()->route('materialMasuk.edit', ['id' => $material_masuk->id])
                    ->with('error', $e->getMessage());
            }
        }

        $dataMaterials = DataMaterial::all();
        return view('page.admin.materialMasuk.ubahMaterialMasuk', [
            'material_masuk' => $material_masuk,
            'dataMaterials' => $dataMaterials,
        ]);
    }

    private function validateEditMaterialKeluar($material_masuk, $new_jumlah)
    {
        $totalMaterialMasuk = MaterialMasuk::where('data_material_id', $material_masuk->data_material_id)
            ->where('waktu', '<=', $material_masuk->waktu)
            ->sum('jumlah');

        $totalMaterialKeluar = MaterialKeluar::where('data_material_id', $material_masuk->data_material_id)
            ->where('waktu', '>=', $material_masuk->waktu)
            ->sum('jumlah');

        if ($totalMaterialKeluar > $totalMaterialMasuk + $new_jumlah) {
            throw new \Exception("Jumlah material keluar lebih besar dari material masuk, hapus material keluar setelah tanggal {$material_masuk->waktu} terlebih dahulu");
        }
    }

    private function updateMaterialMasuk($material_masuk, $request)
    {
        $material_masuk->update([
            'waktu' => $request->waktu,
            'jumlah' => $request->jumlah,
            'satuan' => strtolower($request->satuan),
        ]);
    }

    private function updateEditStokMaterial($material_masuk, $new_jumlah)
    {
        $stok_material = StokMaterial::where('data_material_id', $material_masuk->data_material_id)->firstOrFail();

        $old_stok = $material_masuk->jumlah;
        $stokBaru = $stok_material->stok - $old_stok + $new_jumlah;
        $status = $stok_material->maksimum_stok >= $stokBaru ? 'Tidak Overstock' : 'Overstock';

        $stok_material->update([
            'stok' => $stokBaru,
            'status' => $status
        ]);
    }

    private function updateStokMaterialRecords($material_masuk, $new_jumlah)
    {
        $record = StokMaterialRecord::findOrFail($material_masuk->record_id);
        $old_stok = $material_masuk->jumlah;
        $difference = $new_jumlah - $old_stok;

        $record->update([
            'stok' => $record->stok + $difference,
        ]);

        StokMaterialRecord::where('waktu', '>', $record->waktu)
            ->where('data_material_id', $record->data_material_id)
            ->update(['stok' => DB::raw("stok + $difference")]);
    }

    public function hapusMaterialMasuk($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $material_masuk = MaterialMasuk::findOrFail($id);
                $record = StokMaterialRecord::where('id', $material_masuk->record_id)->firstOrFail();

                $this->updateStokMaterialRecord($record, $material_masuk);
                $this->updateFutureStokMaterialRecords($record, $material_masuk);

                $stok_material = StokMaterial::where('data_material_id', $material_masuk->data_material_id)->firstOrFail();
                $this->validateMaterialKeluar($material_masuk);
                $this->updateStokMaterial($stok_material, $material_masuk);

                $material_masuk->delete();

                return response()->json(['msg' => 'Data yang dipilih telah dihapus']);
            });
        } catch (\Exception $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    private function updateStokMaterialRecord($record, $material_masuk)
    {
        $material_in_records = MaterialMasuk::where('record_id', $record->id)->count();
        if ($material_in_records == 1) {
            $record->delete();
        } else {
            $record->update(['stok' => $record->stok - $material_masuk->jumlah]);
        }
    }

    private function updateFutureStokMaterialRecords($record, $material_masuk)
    {
        StokMaterialRecord::where('waktu', '>', $record->waktu)
            ->where('data_material_id', $record->data_material_id)
            ->update(['stok' => DB::raw("stok - {$material_masuk->jumlah}")]);
    }

    private function validateMaterialKeluar($material_masuk)
    {
        $totalMaterialMasuk = MaterialMasuk::where('data_material_id', $material_masuk->data_material_id)
        ->where('waktu', '<=', $material_masuk->waktu)
        ->sum('jumlah');

        $totalMaterialKeluar = MaterialKeluar::where('data_material_id', $material_masuk->data_material_id)
            ->where('waktu', '<=', $material_masuk->waktu)
            ->sum('jumlah');

        if ($totalMaterialKeluar > $totalMaterialMasuk) {
            throw new \Exception("Jumlah material keluar lebih besar dari material masuk, hapus material keluar setelah tanggal {$material_masuk->waktu} terlebih dahulu");
        }
    }

    private function updateStokMaterial($stok_material, $material_masuk)
    {
        $stokBaru = $stok_material->stok - $material_masuk->jumlah;
        if ($stokBaru < 0) {
            throw new \Exception("Stok tidak boleh kurang dari 0, hapus material keluar {$material_masuk->dataMaterial->kode_material} setelah tanggal {$material_masuk->waktu} terlebih dahulu");
        }

        $status = $stok_material->maksimum_stok >= $stokBaru ? 'Tidak Overstock' : 'Overstock';
        $stok_material->update([
            'stok' => $stokBaru,
            'status' => $status
        ]);
    }

    public function downloadPdf()
    {
        $material = MaterialMasuk::with('dataMaterial')->get();

        $pdf = FacadePdf::loadView('page.admin.materialMasuk.dataMaterialMasukPdf', ['material' => $material]);
        return $pdf->download('data-material-Masuk.pdf');
    }
}
