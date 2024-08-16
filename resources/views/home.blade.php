@extends('layouts.base_admin.base_dashboard')
@section('Halaman Dashboard')
@section('content')
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Dashboard</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Beranda</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
          </ol>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>

  @if (session('status'))
    <div class="alert alert-success alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>

      {{ session('status') }}
    </div>
  @endif

  <section class="content">
    <div class="container">

      <div class="col-12" style="padding: 0px;">
        <div class="card card-secondary">
          <div class="card-header"style="background-color:#212e1f;">
            <div class="d-flex justify-content-between">
              <h3 class="card-title">GRAFIK PERSEDIAAN MATERIAL</h3>

              <div class="d-flex justify-content-end" style="gap: 0.5rem;">
                <select id="chart_year" class="form-control" style="width: fit-content;">
                  <option value="2024" selected>2024</option>
                  <option value="2023">2023</option>
                  <option value="2022">2022</option>
                  <option value="2021">2021</option>
                  <option value="2020">2020</option>
                </select>

                <select id="chart_month" class="form-control" style="width: fit-content;">
                  <option value="1">Januari</option>
                  <option value="2">Februari</option>
                  <option value="3">Maret</option>
                  <option value="4">April</option>
                  <option value="5">Mei</option>
                  <option value="6">Juni</option>
                  <option value="7">Juli</option>
                  <option value="8" selected>Agustus</option>
                  <option value="9">September</option>
                  <option value="10">Oktober</option>
                  <option value="11">November</option>
                  <option value="12">Desember</option>
                </select>
              </div>
            </div>
          </div>

          <div class="p-3">
            <canvas id="chart_material" style="width:100%; height: 400px;"></canvas>
            <div id="legend_chart_material" style="width: 100%; padding-top: 0.75rem"></div>
          </div>
        </div>
      </div>

      <div class="row" style="padding-top: 20px;">
        <div class="col-md-6">
          <div class="card card-secondary">
            <div class="card-header"style="background-color:#212e1f;">
              <h3 class="card-title">TOTAL MATERIAL</h3>

            </div>
            <div class="card-body">
              <h5>{{ $totalMaterial }}</h5>

            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->
        </div>
        <div class="col-md-6">
          <div class="card card-secondary">
            <div class="card-header"style="background-color:#212e1f;">
              <h3 class="card-title">TOTAL PERSEDIAAN SELURUH MATERIAL</h3>


            </div>
            <div class="card-body">
              @if ($stokMaterial >= 0)
                <h5>{{ $stokMaterial }} ton</h5>
              @endif
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->
        </div>
      </div>

      <div class="row" style="padding: 0px;">
        <div class="col-md-6">
          <div class="card card-secondary">
            <div class="card-header"style="background-color:#212e1f;">
              <h3 class="card-title">TOTAL MATERIAL MASUK</h3>


            </div>
            <div class="card-body">
              @if ($totalMaterialMasuk >= 0)
                <h5>{{ $totalMaterialMasuk }} ton</h5>
              @endif

            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->
        </div>
        <div class="col-md-6">
          <div class="card card-secondary">
            <div class="card-header"style="background-color:#212e1f;">
              <h3 class="card-title">TOTAL MATERIAL KELUAR</h3>


            </div>
            <div class="card-body">
              @if ($totalMaterialKeluar >= 0)
                <h5>{{ $totalMaterialKeluar }} ton</h5>
              @endif


            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->
        </div>
      </div>
    </div>
  </section>

@endsection
<script>
  window.chartData = {!! json_encode($chartData) !!};
</script>
<script type="text/javascript" src="{{ asset('js/dashboard_chart.js') }}"></script>
