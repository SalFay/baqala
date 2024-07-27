<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=1">
	<title>Design Summery - Spark Empower</title>
	<style>
    * {
        font-family: Ariel, Calibri, Tahoma sans-serif;
    }


    @media print {
        body {
            -webkit-print-color-adjust: exact;
            -moz-print-color-adjust: exact;
            -ms-print-color-adjust: exact;
            print-color-adjust: exact;
        }


    }


</style>
<!-- CSS only -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
<!-- JavaScript Bundle with Popper -->
</head>
<body>
<div class="container">
	<h3 class="text-center">Design Summery</h3>
	<div class="table-responsive">

		<table class="table table-bordered table-condensed table-striped table-hover">
			<tr>
				<th>Panel Name</th>
				<td>{{$data['bill_of_materials'][0]['name'] ?? ''}}</td>
				<th>Panel Quantity</th>
				<td>{{$data['bill_of_materials'][0]['quantity'] ?? ''}}</td>
			</tr>
			<tr>
				<th>System Size</th>
				<td>{{$data['system_size'] ? round($data['system_size'] /1000, 2). ' kWh':''}}</td>
				<th>System Size STC</th>
				<td>{{$data['system_size_stc']? round($data['system_size_stc'] /1000, 2). ' kWh':''}}</td>
			</tr>
			<tr>
				<th>System Size PTC</th>
				<td>{{$data['system_size_ptc']? round($data['system_size_ptc'] /1000, 2). ' kWh':''}}</td>
				<th>System Size AC</th>
				<td>{{$data['system_size_ac']? round($data['system_size_ac'] /1000, 2). ' kWh':''}}</td>
			</tr>
			@if($data['string_inverters'])
				<tr>
					<th>Inverter Name</th>
					<td> {{$data['string_inverters'][0]['name']}}</td>
					<th>Inverter Manufacturer</th>
					<td> {{$data['string_inverters'][0]['manufacturer']}}</td>
				</tr>
			@endif
			@if($data['energy_production']['monthly'])

				<tr>
					<th>Energy Production (Monthly)</th>
					<td><b>Upto Date: </b>{{$data['energy_production']['up_to_date'] == 1 ? 'Yes' : 'No'?? ''}}</td>
					<td>
						<b>Annual: </b> {{$data['energy_production']['annual'] ? round($data['energy_production']['annual'],2) . ' kWh' : ''}}
					</td>
					<td><b>Annual Offset: </b> {{$data['energy_production']['annual_offset']?? ''}}</td>
				</tr>
				<tr>
					@foreach($data['energy_production']['monthly'] as $m => $value)
						@if($m % 2 == 0)
				</tr>
			@endif
			<th>
				{{date('F', mktime(0, 0, 0, $m+1, 10))}}
			</th>
			<td>
				{{round($value, 2) . ' kWh'}}
			</td>
			@endforeach


			@endif

		</table>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa"
        crossorigin="anonymous"></script>
</body>
</html>
