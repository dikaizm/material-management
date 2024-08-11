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
@if(session('status'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>

    {{ session('status') }}
  </div>
@endif
<section class="content">
    <div class="container">

        <div class="row">
            <div class="col-md-4">
                <div id="chartMaterialMasuk" style="width:100%; height:400px;"></div>
            </div>
            <div class="col-md-4">
                <div id="chartMaterialKeluar" style="width:100%; height:400px;"></div>
            </div>
            <div class="col-md-4">
                <div id="chartStok" style="width:100%; height:400px;"></div>
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
    document.addEventListener('DOMContentLoaded', function () {
        const dates = @json($dates);
            const chartData = @json($chartData);
            const materialNames = @json($materialNames);

            const seriesData = materialNames.map(material => {
                return {
                    name: material,
                    data: dates.map(date => chartData[date][material] || 0)
                };
            });


        Highcharts.chart('chartMaterialMasuk', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'Grafik Material Masuk'
            },
            xAxis: {
                categories: dates
            },
            yAxis: {
                title: {
                    text: 'Jumlah '
                }
            },
            series: seriesData
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const dates = @json($datesOut);
            const chartData = @json($chartDataOut);
            const materialNames = @json($materialNamesOut);

            const seriesData = materialNames.map(material => {
                return {
                    name: material,
                    data: dates.map(date => chartData[date][material] || 0)
                };
            });


        Highcharts.chart('chartMaterialKeluar', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'Grafik Material Keluar'
            },
            xAxis: {
                categories: dates
            },
            yAxis: {
                title: {
                    text: 'Jumlah '
                }
            },
            series: seriesData
        });
    });



    document.addEventListener('DOMContentLoaded', function () {
        var chart = Highcharts.chart('chartStok', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'Grafik Stok Material'
            },
            xAxis: {
                categories: {!! $stokLabels !!}
            },
            yAxis: {
                title: {
                    text: 'Jumlah Material'
                }
            },
            series: [
                {
                    name: 'Stok Material',
                    data: {!! $stok !!}
                },
                {
                    name: 'Maksimum Stok',
                    data: {!! $maxStok !!}
                }
            ]
        });
    });
</script>
