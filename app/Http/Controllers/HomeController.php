<?php

namespace App\Http\Controllers;

use App\Models\DataMaterial;
use App\Models\MaterialKeluar;
use App\Models\MaterialMasuk;
use App\Models\StokMaterial;
use App\Models\StokMaterialRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $timezone = 'Asia/Jakarta';

        $current_month = Carbon::now($timezone)->format('m');
        $current_year = Carbon::now($timezone)->format('Y');

        $material_codes = DataMaterial::pluck('kode_material', 'id');

        $material_ins = MaterialMasuk::whereMonth('waktu', $current_month)->whereYear('waktu', $current_year)->get();
        $material_outs = MaterialKeluar::whereMonth('waktu', $current_month)->whereYear('waktu', $current_year)->get();
        $material_stock_records = StokMaterialRecord::whereMonth('waktu', $current_month)->whereYear('waktu', $current_year)->get();

        function getPrevStockForMaterial($current_year, $current_month, $material_id, $material_code)
        {
            $prev_month = $current_month == 1 ? 12 : $current_month - 1;
            $prev_year = $current_month == 1 ? $current_year - 1 : $current_year;

            $prev_record = StokMaterialRecord::where('data_material_id', $material_id)
                ->whereMonth('waktu', $prev_month)
                ->whereYear('waktu', $prev_year)
                ->orderBy('waktu', 'desc')
                ->first();

            if ($prev_record) {
                return [$material_code => $prev_record->stok];
            } else {
                if ($prev_year >= 2020) {
                    // Recursively call the function for the previous month
                    return getPrevStockForMaterial($prev_year, $prev_month, $material_id, $material_code);
                } else {
                    // No data found all the way back to 2020
                    return [$material_code => 0]; // or any default value you prefer
                }
            }
        }

        $prev_stock = [];

        foreach ($material_codes as $material_id => $material_code) {
            $result = getPrevStockForMaterial($current_year, $current_month, $material_id, $material_code);
            $prev_stock = array_merge($prev_stock, $result);
        }

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

        // dd($mapped_material_in_data, $mapped_material_out_data, $mapped_material_stock_data);

        // get stok material
        $material_stock = StokMaterial::get();
        $max_stock = $material_stock->max('maksimum_stok');

        return view('home', [
            'chartData' => [
                'material_ins' => $mapped_material_in_data,
                'material_outs' => $mapped_material_out_data,
                'material_stock' => $mapped_material_stock_data,
                'max_stock' => $max_stock,
                'material_codes' => $material_codes,
                'year' => $current_year,
                'month' => $current_month,
                'prev_stock' => $prev_stock,
            ],

            'totalMaterial' => DataMaterial::all()->count(),
            'totalMaterialMasuk' => MaterialMasuk::sum('jumlah'),
            'totalMaterialKeluar' => MaterialKeluar::sum('jumlah'),
            'stokMaterial' => StokMaterial::sum('stok')
        ]);
    }

    public function profile()
    {
        return view('page.admin.profile');
    }

    public function updateprofile(Request $request)
    {
        $usr = User::findOrFail(Auth::user()->id);
        if ($request->input('type') == 'change_profile') {
            $this->validate($request, [
                'name' => 'string|max:200|min:3',
                'email' => 'string|min:3|email',
                'user_image' => 'image|mimes:jpg,png,jpeg,gif,svg|max:1024'
            ]);
            $img_old = Auth::user()->user_image;
            if ($request->file('user_image')) {
                # delete old img
                if ($img_old && file_exists(public_path() . $img_old)) {
                    unlink(public_path() . $img_old);
                }
                $nama_gambar = time() . '_' . $request->file('user_image')->getClientOriginalName();
                $upload = $request->user_image->storeAs('public/admin/user_profile', $nama_gambar);
                $img_old = Storage::url($upload);
            }
            $usr->update([
                'name' => $request->name,
                'email' => $request->email,
                'user_image' => $img_old
            ]);
            return redirect()->route('profile')->with('status', 'Perubahan telah tersimpan');
        } elseif ($request->input('type') == 'change_password') {
            $this->validate($request, [
                'password' => 'min:8|confirmed|required',
                'password_confirmation' => 'min:8|required',
            ]);
            $usr->update([
                'password' => Hash::make($request->password)
            ]);
            return redirect()->route('profile')->with('status', 'Perubahan telah tersimpan');
        }
    }
}
