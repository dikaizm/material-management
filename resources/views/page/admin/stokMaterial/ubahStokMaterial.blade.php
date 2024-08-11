@extends('layouts.base_admin.base_dashboard')
@section('judul', 'Edit Stok Material')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Edit Stok Material</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home') }}">Beranda</a>
                    </li>
                    <li class="breadcrumb-item active">Edit Stok Material</li>
                </ol>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">
    @if(session('status'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-check"></i> Berhasil!</h4>
        {{ session('status') }}
    </div>
    @endif
    <form method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">Informasi Stok Material</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="inputNamaMaterial">Nama Material</label>
                            <select
                                id="inputNamaMaterial"
                                name="nama_material"
                                class="form-control @error('nama_material') is-invalid @enderror"
                                required="required" disabled>
                                <option value="" selected disabled>Pilih Nama Material</option>
                                @foreach($dataMaterials as $material)
                                <option value="{{ $material->id }}" {{ old('nama_material', $stok_material->data_material_id) == $material->id ? 'selected' : '' }}>{{ $material->nama_material }}</option>
                                @endforeach
                            </select>
                            @error('nama_material')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="inputKodeMaterial">Kode Material</label>
                            <select
                                id="inputKodeMaterial"
                                name="kode_material"
                                class="form-control @error('kode_material') is-invalid @enderror"
                                required="required" disabled>
                                <option value="" selected disabled>Pilih Kode Material</option>
                                @foreach($dataMaterials as $material)
                                <option value="{{ $material->id }}" {{ old('kode_material', $stok_material->data_material_id) == $material->id ? 'selected' : '' }}>{{ $material->kode_material }}</option>
                                @endforeach
                            </select>
                            @error('kode_material')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="inputStok">Stok</label>
                            <input
                                type="number"
                                id="inputStok"
                                name="stok"
                                class="form-control @error('stok') is-invalid @enderror"
                                placeholder="Masukkan Stok"
                                value="{{ old('stok', $stok_material->stok) }}"
                                required="required"
                                autocomplete="stok" disabled>
                            @error('stok')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="inputMaksimumStok">Maksimum Stok</label>
                            <input
                                type="number"
                                id="inputMaksimumStok"
                                name="maksimum_stok"
                                class="form-control @error('maksimum_stok') is-invalid @enderror"
                                placeholder="Masukkan Maksimum Stok"
                                value="{{ old('maksimum_stok', $stok_material->maksimum_stok) }}"
                                required="required"
                                autocomplete="maksimum_stok">
                            @error('maksimum_stok')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <a href="{{ route('stokMaterial.index') }}" class="btn btn-danger">Batal</a>
                <input type="submit" value="Update Stok Material" class="btn btn-success float-right">
            </div>
        </div>
    </form>
</section>
<!-- /.content -->

@endsection
