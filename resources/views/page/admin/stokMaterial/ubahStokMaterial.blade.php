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
    @if (session('status'))
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
                <label for="inputMaksimumStok">Maksimum Stok</label>
                <input type="number" id="inputMaksimumStok" name="maksimum_stok"
                  class="form-control @error('maksimum_stok') is-invalid @enderror" placeholder="Masukkan Maksimum Stok"
                  value="{{ old('maksimum_stok', $max_stock) }}" required="required"
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
