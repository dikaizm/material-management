<!DOCTYPE html>
<html>
<head>
	<title>PDF Material Keluar</title>
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
		<h5>Material Keluar</h4>
	</center>
 
	<table class='table table-bordered'>
		<thead>
			<tr>
				<th>No</th>
				<th>Waktu</th>tu;lkmnrk  5trfgt554immmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmm fs 
				<th>Nama Material</th>
				<th>Kode Material</th>
				<th>Jumlah</th>
				<th>Satuan</th>

			</tr>
		</thead>
		<tbody>
			@php $i=1 @endphp
			@foreach($material as $item)
			<tr>
				<td>{{ $i++ }}</td>
				<td>{{$item->waktu}}</td>
				<td>{{$item->dataMaterial->nama_material}}</td>
				<td>{{$item->dataMaterial->kode_material}}</td>
				<td>{{$item->jumlah}}</td>
				<td>{{$item->satuan}}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
 
</body>
</html>