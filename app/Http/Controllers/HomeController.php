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
        $data = MaterialMasuk::with('dataMaterial')
        ->where('waktu', '>=', Carbon::now()->subDays(5))
        ->get()
        ->sortBy('waktu')
        ->groupBy(function ($item) {
            return Carbon::parse($item->waktu)->format('Y-m-d');
        });

    $chartData = [];
    $materialNames = [];

    foreach ($data as $date => $materials) {
        $dailyData = [];
        foreach ($materials as $material) {
            $materialName = $material->dataMaterial->nama_material;
            if (!in_array($materialName, $materialNames)) {
                $materialNames[] = $materialName;
            }
            if (!isset($dailyData[$materialName])) {
                $dailyData[$materialName] = 0;
            }
            $dailyData[$materialName] += $material->jumlah;
        }
        $chartData[$date] = $dailyData;
    }

    $dates = array_keys($chartData);

    $materialKeluarGroupedData = MaterialKeluar::with('dataMaterial')
        ->where('waktu', '>=', Carbon::now()->subDays(5))
        ->get()
        ->sortBy('waktu')
        ->groupBy(function ($item) {
            return Carbon::parse($item->waktu)->format('Y-m-d');
        });

    $materialKeluarChartData = [];
    $materialKeluarNames = [];

    foreach ($materialKeluarGroupedData as $date => $materials) {
        $dailyKeluarData = [];
        foreach ($materials as $material) {
            $materialKeluarName = $material->dataMaterial->nama_material;
            if (!in_array($materialKeluarName, $materialKeluarNames)) {
                $materialKeluarNames[] = $materialKeluarName;
            }
            if (!isset($dailyKeluarData[$materialKeluarName])) {
                $dailyKeluarData[$materialKeluarName] = 0;
            }
            $dailyKeluarData[$materialKeluarName] += $material->jumlah;
        }
        $materialKeluarChartData[$date] = $dailyKeluarData;
    }

    $materialKeluarDates = array_keys($materialKeluarChartData);

    $stokMaterial = StokMaterial::with('dataMaterial')->get();

    $stokLabels = [];
    $stok = [];
    $maxStok = [];

    foreach ($stokMaterial as $material) {
        $stokLabels[] = $material->dataMaterial->nama_material;
        $stok[] = $material->stok;
        $maxStok[] = $material->maksimum_stok;
    }
    
    return view('home', [
        'dates' => $dates,
        'chartData' => $chartData,
        'materialNames' => $materialNames,

        'datesOut' => $materialKeluarDates,
        'chartDataOut' => $materialKeluarChartData,
        'materialNamesOut' => $materialKeluarNames,

        'stokLabels' => json_encode(array_values($stokLabels)),
        'stok' => json_encode(array_values($stok)),
        'maxStok' => json_encode(array_values($maxStok)),

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
                if ($img_old && file_exists(public_path().$img_old)) {
                    unlink(public_path().$img_old);
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
