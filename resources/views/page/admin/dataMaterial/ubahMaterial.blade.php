@extends('layouts.base_admin.base_dashboard')
@section('judul', 'Ubah Data Material')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Ubah Data Material</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home') }}">Beranda</a>
                    </li>
                    <li class="breadcrumb-item active">Ubah Data Material</li>
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
                        <h3 class="card-title">Informasi Data Material</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="inputNamaMaterial">Nama Material</label>
                            <input
                                type="text"
                                id="inputNamaMaterial"
                                name="nama_material"
                                class="form-control @error('nama_material') is-invalid @enderror"
                                placeholder="Masukkan Nama Material"
                                value="{{ $material->nama_material }}"
                                required="required"
                                autocomplete="nama_material">
                            @error('nama_material')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="inputKodeMaterial">Kode Material</label>
                            <input
                                type="text"
                                id="inputKodeMaterial"
                                name="kode_material"
                                class="form-control @error('kode_material') is-invalid @enderror"
                                placeholder="Masukkan Kode Material"
                                value="{{ $material->kode_material }}"
                                required="required"
                                autocomplete="kode_material">
                            @error('kode_material')
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
                <a href="javascript:history.back()" class="btn btn-danger">Batal</a>
                <input type="submit" value="Ubah Data Material" class="btn btn-success float-right">
            </div>
        </div>
    </form>
</section>
<!-- /.content -->

@endsection

@section('script_footer')
<script>
    // JavaScript for handling file input and preview image
    inputFoto.onchange = evt => {
        const [file] = inputFoto.files
        if (file) {
            prevImg.src = URL.createObjectURL(file)
        }
    }
</script>
@endsection
