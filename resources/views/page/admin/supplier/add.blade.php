@extends('layouts.base_admin.base_dashboard')
@section('judul', 'Tambah Supplier')

@section('content')
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Tambah Supplier</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item">
              <a href="{{ route('home') }}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">Tambah Supplier</li>
          </ol>
        </div>
      </div>
    </div>
    <!-- /.container-fluid -->
  </section>

  <!-- Main content -->
  <section class="content" style="padding-bottom: 2rem;">

    @if (session('success'))
      <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-check"></i> Berhasil!</h4>
        {{ session('success') }}
      </div>
    @endif

    @if (session('error'))
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
            <div class="card-header"style="background-color:#212e1f;">
              <h3 class="card-title">Informasi Supplier</h3>

              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                  <i class="fas fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <div class="form-group">
                <label for="register_date">Tanggal Daftar</label>
                <input type="date" id="register_date" name="register_date"
                  class="form-control @error('register_date') is-invalid @enderror" value="{{ old('register_date') }}"
                  required="required" autocomplete="register_date">
                @error('register_date')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                @enderror
              </div>

              <div class="form-group">
                <label for="supplier_name">Nama Supplier</label>
                <input type="text" id="supplier_name" name="supplier_name"
                  class="form-control @error('supplier_name') is-invalid @enderror" placeholder="Masukkan nama supplier"
                  value="{{ old('supplier_name') }}" required="required" autocomplete="supplier_name">
                @error('supplier_name')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                @enderror
              </div>

              <div class="form-group">
                <label for="supplier_code">Kode Supplier</label>
                <input type="text" id="supplier_code" name="supplier_code"
                  class="form-control @error('supplier_code') is-invalid @enderror" placeholder="Masukkan kode supplier"
                  value="{{ old('supplier_code') }}" required="required" autocomplete="supplier_code">
                @error('supplier_code')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                @enderror
              </div>

              <div class="form-group">
                <label for="supplier_phone">Telepon</label>
                <input type="tel" id="supplier_phone" name="supplier_phone"
                  class="form-control @error('supplier_phone') is-invalid @enderror" placeholder="Masukkan nomor telepon"
                  value="{{ old('supplier_phone') }}" required="required" autocomplete="supplier_phone">
                @error('supplier_phone')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                @enderror
              </div>

              <div class="form-group">
                <label for="address">Alamat</label>
                <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror"
                  placeholder="Masukkan alamat" rows="2" required="required" autocomplete="address">{{ old('address') }}</textarea>
                @error('address')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                @enderror
              </div>

            </div>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
      </div>

      <div class="row">
        <div class="col-md-6">
          <a href="{{ route('supplier.index') }}" class="btn btn-danger">Batal</a>
          <input type="submit" value="Tambah Supplier" class="btn btn-success float-right">
        </div>
      </div>

    </form>
  </section>
  <!-- /.content -->

@endsection
