<?php

namespace App\Http\Controllers;

use App\Models\DataMaterial;
use App\Models\MaterialKeluar;
use App\Models\MaterialMasuk;
use App\Models\StokMaterial;
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
        $current_date = Carbon::now($timezone);
        $min_date = Carbon::now($timezone)->subDays(6);

        // Generate dates for the last 6 days in array
        $dates = [];
        for ($i = 0; $i < 6; $i++) {
            $dates[] = Carbon::now($timezone)->subDays($i)->format('d-m-Y');
        }

        $material_codes = DataMaterial::pluck('kode_material');

        $material_in_data = MaterialMasuk::whereBetween('waktu', [$min_date, $current_date])->get()->groupBy(function ($date) {
            return Carbon::parse($date->waktu)->format('d-m-Y');
        });
        $material_out_data = MaterialKeluar::whereBetween('waktu', [$min_date, $current_date])->get()->groupBy(function ($date) {
            return Carbon::parse($date->waktu)->format('d-m-Y');
        });

        $material_stock = StokMaterial::get();

        $mapped_material_in_data = [];
        $mapped_material_out_data = [];
        $mapped_material_stock = [];

        $acc_material_in = [];
        $acc_material_out = [];

        foreach ($dates as $date) {
            $mapped_material_in_data[$date] = [];
            $mapped_material_out_data[$date] = [];
            $mapped_material_stock[$date] = [];

            $material_in_sum = 0;
            $material_out_sum = 0;

            foreach ($material_codes as $material_code) {
                $material_in_sum = $material_in_data->get($date, collect())
                    ->where('dataMaterial.kode_material', $material_code)
                    ->sum('jumlah');

                $material_out_sum = $material_out_data->get($date, collect())
                    ->where('dataMaterial.kode_material', $material_code)
                    ->sum('jumlah');

                $material_stock_find = $material_stock->where('dataMaterial.kode_material', $material_code)->first();
                if ($material_stock_find) {
                    $material_stock_amount = $material_stock_find->stok;
                } else {
                    $material_stock_amount = 0;
                }

                if ($date == $current_date->format('d-m-Y')) {
                    $mapped_material_stock[$date][$material_code] = $material_stock_amount;
                } else {
                    $mapped_material_stock[$date][$material_code] = $material_stock_amount - ($acc_material_in[$material_code] - $acc_material_out[$material_code]);
                }

                if (!isset($acc_material_in[$material_code])) {
                    $acc_material_in[$material_code] = $material_in_sum;
                } else {
                    $acc_material_in[$material_code] += $material_in_sum;
                }

                if (!isset($acc_material_out[$material_code])) {
                    $acc_material_out[$material_code] = $material_out_sum;
                } else {
                    $acc_material_out[$material_code] += $material_out_sum;
                }

                $mapped_material_in_data[$date][$material_code] = $material_in_sum;
                $mapped_material_out_data[$date][$material_code] = $material_out_sum;
            }
        }

        // dd($acc_material_in, $acc_material_out);

        return view('home', [
            'chartData' => [
                'material_in' => $mapped_material_in_data,
                'material_out' => $mapped_material_out_data,
                'material_stock' => $mapped_material_stock,
                'max_stock' => 20,
                'dates' => $dates
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
