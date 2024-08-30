@extends('layouts.base_admin.base_dashboard')
@section('judul', 'List Material Masuk')
@section('script_head')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css">
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Material Masuk</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home') }}">Beranda</a>
                    </li>
                    <li class="breadcrumb-item active">Material Masuk</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">

    <!-- Default box -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"></h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
                </button>
                <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0" style="margin: 20px">
            <div style="display: flex; justify-content: space-between; align-items: end; width: 100%; margin-bottom: 20px;">
                <div class="d-flex" style="gap: 0.5rem;">
                    <div class="w-100">
                        <label for="start_date">Start Date:</label>
                        <input type="date" id="start_date" class="form-control">
                    </div>
                    <div class="w-100">
                        <label for="end_date">End Date:</label>
                        <input type="date" id="end_date" class="form-control">
                    </div>
                    <div class="d-flex align-items-end" style="gap: 0.5rem;">
                        <button id="filter_button" class="btn btn-primary">Filter</button>
                        <button id="filter_reset_button" class="btn btn-light">Reset</button>
                    </div>
                </div>

                <a href="materialMasuk/pdf" class="btn btn-success" style="height: fit-content;">Download PDF</a>
            </div>

            <table id="previewMaterialMasuk" class="table table-striped table-bordered display" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Waktu</th>
                        <th>Nama Material</th>
                        <th>Kode Material</th>
                        <th>Jumlah</th>
                        <th>Satuan</th>
                        <th>Oleh</th>
                        @if (auth()->user()->hasRole('direktur'))
                            <th>Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->

</section>
@endsection

@section('script_footer')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(document).ready(function () {
        var t = $('#previewMaterialMasuk').DataTable({
            "serverSide": true,
            "processing": true,
            "ajax": {
                "url": "{{ route('materialMasuk.dataTable') }}",
                "dataType": "json",
                "type": "POST",
                "data": function (d) {
                    d._token = "{{ csrf_token() }}";
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            "columns": [
                {
                    "data": null,
                    "sortable": false,
                    "searchable": false
                },
                { "data": "waktu" },
                { "data": "nama_material" },
                { "data": "kode_material" },
                { "data": "jumlah" },
                { "data": "satuan" },
                { "data": "created_by" },
                @if (auth()->user()->hasRole('direktur'))
                { "data": "options" }
                @endif
            ],
            "language": {
                "decimal": "",
                "emptyTable": "Tak ada data yang tersedia pada tabel ini",
                "info": "Menampilkan _START_ hingga _END_ dari _TOTAL_ entri",
                "infoEmpty": "Menampilkan 0 hingga 0 dari 0 entri",
                "infoFiltered": "(difilter dari _MAX_ total entri)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Tampilkan _MENU_ entri",
                "loadingRecords": "Loading...",
                "processing": "Sedang Mengambil Data...",
                "search": "Pencarian:",
                "zeroRecords": "Tidak ada data yang cocok ditemukan",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                },
                "aria": {
                    "sortAscending": ": aktifkan untuk mengurutkan kolom ascending",
                    "sortDescending": ": aktifkan untuk mengurutkan kolom descending"
                }
            }
        });

        t.on('draw.dt', function () {
            var PageInfo = $('#previewMaterialMasuk').DataTable().page.info();
            t.column(0, { page: 'current' }).nodes().each(function (cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });

        // hapus data
        $('#previewMaterialMasuk').on('click', '.hapusData', function () {
            var id = $(this).data("id");
            var url = $(this).data("url");
            Swal.fire({
                title: 'Apa kamu yakin?',
                text: "Kamu tidak akan dapat mengembalikan ini!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {
                            "id": id,
                            "_token": "{{csrf_token()}}"
                        },
                        success: function (response) {
                            Swal.fire('Terhapus!', response.msg, 'success');
                            $('#previewMaterialMasuk').DataTable().ajax.reload();
                        },
                        error: function (xhr) {
                            Swal.fire('Gagal!', xhr.responseJSON.msg, 'error');
                        }
                    });
                }
            })
        });

        // Add event listener for the filter button
        $('#filter_button').on('click', function() {
            t.ajax.reload();
        });

        $('#filter_reset_button').on('click', function() {
            $('#start_date').val('');
            $('#end_date').val('');
            t.ajax.reload();
        });
    });
</script>
@endsection
