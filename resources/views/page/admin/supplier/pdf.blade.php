<!DOCTYPE html>
<html>
<head>
	<title>PDF Supplier</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body>
	<style type="text/css">
		table tr td,
		table tr th{
			font-size: 9pt;
		}
	</style>
	<center>
		<h5>Supplier</h4>
	</center>

	<table class='table table-bordered'>
		<thead>
			<tr>
				<th>No</th>
				<th>Tanggal Daftar</th>
				<th>Nama</th>
				<th>Kode</th>
				<th>Telepon</th>
				<th>Alamat</th>

			</tr>
		</thead>
		<tbody>
			@php $i=1 @endphp
			@foreach($data as $d)
			<tr>
				<td>{{ $i++ }}</td>
				<td>{{$d->register_date}}</td>
				<td>{{$d->name}}</td>
				<td>{{$d->code}}</td>
				<td>{{$d->phone}}</td>
				<td>{{$d->address}}</td>
			</tr>
			@endforeach
		</tbody>
	</table>

</body>
</html>
