<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;

class CustomerController extends Controller
{
    public function index()
    {
        return view('page.admin.customer.index');
    }

    public function dataTable(Request $request)
    {
        $totalFilteredRecord = $totalDataRecord = $draw_val = "";
        $column_list = array(
            0 => 'register_date',
            1 => 'id',
            2 => 'name',
            3 => 'code',
            4 => 'phone',
            5 => 'address',
        );

        $query = Customer::query();

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
                $query->whereBetween('register_date', [$start_date, $end_date]);
            }
        }

        $totalDataRecord = $query->count();

        $limit_val = $request->input('length');
        $start_val = $request->input('start');
        $order_val = $column_list[$request->input('order.0.column')];
        $dir_val = $request->input('order.0.dir');

        if (!empty($request->input('search.value'))) {
            $search_val = $request->input('search.value');

            $query->where(function ($query) use ($search_val) {
                $query->where('name', 'LIKE', "%{$search_val}%")
                    ->orWhere('code', 'LIKE', "%{$search_val}%")
                    ->orWhere('phone', 'LIKE', "%{$search_val}%")
                    ->orWhere('address', 'LIKE', "%{$search_val}%");
            });

            $totalFilteredRecord = $query->count();
        } else {
            $totalFilteredRecord = $totalDataRecord; // Set this when no search is applied
        }

        $data = $query->offset($start_val)
            ->limit($limit_val)
            ->orderBy($order_val, $dir_val)
            ->get();

        $data_arr = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $register_date = date('d-m-Y', strtotime($d->register_date));

                $url_edit = route('customer.edit', ['id' => $d->id]);
                $url_delete = route('customer.delete', ['id' => $d->id]);

                $nested_data['register_date'] = $register_date;
                $nested_data['name'] = $d->name;
                $nested_data['code'] = $d->code;
                $nested_data['phone'] = $d->phone;
                $nested_data['address'] = $d->address;

                if (auth()->user()->hasRole('admin')) {
                    $nested_data['options'] = "<a href='$url_edit'><i class='fas fa-edit fa-lg'></i></a>
                    <a style='border: none; background-color:transparent;' class='hapusData' data-id='$d->id' data-url='$url_delete'><i class='fas fa-trash fa-lg text-danger'></i></a>";
                }

                $data_arr[] = $nested_data;
            }
        }

        $draw_val = intval($request->input('draw'));
        echo json_encode([
            "draw" => $draw_val,
            "recordsTotal" => intval($totalDataRecord),
            "recordsFiltered" => intval($totalFilteredRecord),
            "data" => $data_arr
        ]);
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {
            $validatedData = $request->validate([
                'register_date' => 'required|date',
                'customer_name' => 'required|min:3',
                'customer_code' => 'required|min:3',
                'customer_phone' => 'required|numeric|digits_between:10,15',
                'address' => 'required|min:5',
            ]);

            // Check if code already exists
            $data = Customer::where('code', strtoupper($request->customer_code))->first();
            if ($data) {
                return redirect()->back()->withInput()->with('error', 'Kode customer sudah digunakan');
            }

            Customer::create([
                'register_date' => $request->register_date,
                'name' => $request->customer_name,
                'code' => strtoupper($request->customer_code),
                'phone' => $request->customer_phone,
                'address' => $request->address,
            ]);

            return redirect()->route('customer.add')->with('success', 'Customer berhasil ditambahkan');
        }

        if ($request->isMethod('get')) {
            return view('page.admin.customer.add');
        }
    }

    public function edit($id, Request $request)
    {
        $data = Customer::find($id);

        if ($request->isMethod('post')) {
            if (!$data) {
                return redirect()->route('customer.index')->with('error', 'Data tidak ditemukan');
            }

            if ($request->isMethod('post')) {
                $validatedData = $request->validate([
                    'register_date' => 'required|date',
                    'customer_name' => 'required|min:3',
                    'customer_code' => 'required|min:3',
                    'customer_phone' => 'required|numeric|digits_between:10,15',
                    'address' => 'required|min:5',
                ]);

                // Check if code already exists
                $check_code = Customer::where('code', strtoupper($request->customer_code))
                    ->where('id', '!=', $id)
                    ->first();
                if ($check_code) {
                    return redirect()->back()->withInput()->with('error', 'Kode customer sudah digunakan');
                }

                $data->register_date = $request->register_date;
                $data->name = $request->customer_name;
                $data->code = strtoupper($request->customer_code);
                $data->phone = $request->customer_phone;
                $data->address = $request->address;
                $data->save();

                return redirect()->route('customer.index')->with('success', 'Data berhasil diubah');
            }
        }

        if ($request->isMethod('get')) {
            return view('page.admin.customer.edit', compact('data'));
        }
    }

    public function delete($id)
    {
        try {
            $data = Customer::find($id);
            if ($data) {
                $data->delete();
                return response()->json(['msg' => 'Data berhasil dihapus']);
            }

            return response()->json(['msg' => 'Data tidak ditemukan'], 404);
        } catch (\Exception $e) {
            return response()->json(['msg' => 'Terjadi kesalahan saat menghapus data'], 500);
        }
    }

    public function pdf()
    {
        $data = Customer::all();
        $pdf = FacadePdf::loadView('page.admin.customer.pdf', compact('data'));
        return $pdf->download('customer.pdf');
    }
}
