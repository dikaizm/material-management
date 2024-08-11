@extends('layouts.base_admin.base_dashboard')
@section('judul', 'Tambah Stok Material')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Tambah Stok Material</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home') }}">Beranda</a>
                    </li>
                    <li class="breadcrumb-item active">Tambah Stok Material</li>
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
                    <div class="card-header"style="background-color:#212e1f;">
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
                                required="required">
                                <option value="" selected disabled>Pilih Nama Material</option>
                                @foreach($dataMaterials as $material)
                                <option value="{{ $material->id }}" {{ old('nama_material') == $material->id ? 'selected' : '' }}>{{ $material->nama_material }}</option>
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
                                required="required">
                                <option value="" selected disabled>Pilih Kode Material</option>
                                @foreach($dataMaterials as $material)
                                <option value="{{ $material->id }}" {{ old('kode_material') == $material->id ? 'selected' : '' }}>{{ $material->kode_material }}</option>
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
                                value="{{ old('stok') }}"
                                required="required"
                                autocomplete="stok">
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
                                value="{{ old('maksimum_stok') }}"
                                required="required"
                                autocomplete="maksimum_stok">
                            @error('maksimum_stok')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <div class="form-check">
                                <input
                                    class="form-check-input @error('status') is-invalid @enderror"
                                    type="radio"
                                    name="status"
                                    id="status1"
                                    value="tidak overstock"
                                    {{ old('status') == 'tidak overstock' ? 'checked' : '' }}
                                    required="required">
                                <label class="form-check-label" for="status1">
                                    Tidak Overstock
                                </label>
                            </div>
                            <div class="form-check">
                                <input
                                    class="form-check-input @error('status') is-invalid @enderror"
                                    type="radio"
                                    name="status"
                                    id="status2"
                                    value="overstock"
                                    {{ old('status') == 'overstock' ? 'checked' : '' }}>
                                <label class="form-check-label" for="status2">
                                    Overstock
                                </label>
                            </div>
                            @error('status')
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
            <div class="col-12">
                <a href="{{ route('stokMaterial.index') }}" class="btn btn-danger">Batal</a>
                <input type="submit" value="Tambah Stok Material" class="btn btn-success float-right">
            </div>
        </div>
    </form>
</section>
<!-- /.content -->

@endsection
