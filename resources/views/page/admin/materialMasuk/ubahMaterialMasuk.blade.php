@extends('layouts.base_admin.base_dashboard')
@section('judul', 'Edit Material Masuk')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Edit Material Masuk</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home') }}">Beranda</a>
                    </li>
                    <li class="breadcrumb-item active">Edit Material Masuk</li>
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

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-exclamation-circle"></i> Gagal!</h4>
        {{ session('error') }}
    </div>
    @endif

    <form method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">Informasi Material Masuk</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="inputWaktu">Waktu</label>
                            <input
                                type="date"
                                id="inputWaktu"
                                name="waktu"
                                class="form-control @error('waktu') is-invalid @enderror"
                                value="{{ old('waktu', $material_masuk->waktu) }}"
                                required="required"
                                autocomplete="waktu">
                            @error('waktu')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="inputNamaMaterial">Nama Material</label>
                            <select
                                id="inputNamaMaterial"
                                name="nama_material"
                                class="form-control @error('nama_material') is-invalid @enderror"
                                required="required" disabled>
                                <option value="" selected disabled>Pilih Nama Material</option>
                                @foreach($dataMaterials as $material)
                                <option value="{{ $material->id }}" {{ old('nama_material', $material_masuk->data_material_id) == $material->id ? 'selected' : '' }}>{{ $material->nama_material }}</option>
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
                                <option value="{{ $material->id }}" {{ old('kode_material', $material_masuk->data_material_id) == $material->id ? 'selected' : '' }}>{{ $material->kode_material }}</option>
                                @endforeach
                            </select>
                            @error('kode_material')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="inputJumlah">Jumlah</label>
                            <input
                                type="number"
                                id="inputJumlah"
                                name="jumlah"
                                class="form-control @error('jumlah') is-invalid @enderror"
                                placeholder="Masukkan Jumlah"
                                value="{{ old('jumlah', $material_masuk->jumlah) }}"
                                required="required"
                                autocomplete="jumlah">
                            @error('jumlah')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="inputSatuan">Satuan</label>
                            <input
                                type="text"
                                id="inputSatuan"
                                name="satuan"
                                class="form-control @error('satuan') is-invalid @enderror"
                                placeholder="Masukkan Satuan"
                                value="{{ old('satuan', $material_masuk->satuan) }}"
                                required="required"
                                autocomplete="satuan">
                            @error('satuan')
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
                <a href="{{ route('materialMasuk.index') }}" class="btn btn-danger">Batal</a>
                <input type="submit" value="Update Material Masuk" class="btn btn-success float-right">
            </div>
        </div>
    </form>
</section>
<!-- /.content -->

@endsection
