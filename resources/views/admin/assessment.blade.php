<style>
    * {
        font-family: Ariel, Calibri, Tahoma sans-serif;
    }

    th {
        width: 140px !important;
    }

    @media print {
        body {
            -webkit-print-color-adjust: exact;
            -moz-print-color-adjust: exact;
            -ms-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .col-sm-4 {
            flex: 0 0 auto;
            width: 50% !important;
        }

        .col-sm-6 {
            flex: 0 0 auto;
            width: 50% !important;
        }
    }


    body {
        -webkit-print-color-adjust: exact;
        -moz-print-color-adjust: exact;
        -ms-print-color-adjust: exact;
        print-color-adjust: exact;
        font-family: "Quicksand", sans-serif;
    }

    .box {
        background: white;
        text-align: center;
        margin-top: 150px;
        width: 50%;
        border-radius: 3%;
        margin-left: 23%;
        opacity: 0.9;
    }

    hr {
        margin: 0 !important;
        color: inherit;
        border: 0;
        border-top: 1px solid;
        opacity: .25;
        width: 50% !important;
        /* text-align: center; */
    }

    .background-img {
        position: absolute;
        left: 0px;
        top: 0px;
        z-index: -1;
    }

    .details {
        margin: 0px;
    }

    .stronger {
        font-size: 50px;
    }

    h3 {
        font-size: 35px !important;
    }

    .small-div {
        border: 1px solid;
        padding: 25px;
        text-align: center;
    }

    .steps {
        padding-left: 50%;
        padding-top: 20px;
        text-align: left;
    }

    @page {
        size: A4 portrait;
        margin: 0;
    }

    @media print {
        body {
            -webkit-print-color-adjust: exact;
            -moz-print-color-adjust: exact;
            -ms-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        footer {
            page-break-after: always;
        }


        @page {
            size: A4 portrait;
            margin: 0;

        }

    }


</style>
<!-- CSS only -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
<!-- JavaScript Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa"
        crossorigin="anonymous"></script>
<div class="container-fluid">
	<br>
	<br>
	<div class="row">

		<div class="col-sm-4" style="margin-top:40px">
			<img alt="Image" class="img img-responsive"
			     src="{{asset("assets/emp-logo1.png")}}" style="width:280px; margin-right: 10px"/>
			<div class="row">
				<h2> Site Assessment</h2>
			</div>
		</div>
		<div class="col-sm-6">
			<table class="table table-bordered table-striped">
				<tr>
					<th>Customer</th>
					<td> {{$aurora->customer_one_fname . ' ' . $aurora->customer_one_lname}}</td>
				</tr>
				<tr>
					<th>Address</th>
					<td> @if(!empty($aurora->info->location))
							{{$aurora->info->location }} <br>
							{{stateName($aurora->state) . ' , ' . cityName($aurora->city) . ' , '. $aurora->zipcode }}
						@else
							{{$aurora->info->full_address}}
						@endif
					</td>
				</tr>
				<tr>
					<th>System Size</th>
					<td> @if($consumptions)

						@endif</td>
				</tr>
				<tr>
					<th>Yr 1 Production</th>
					<td> 1049 kWh</td>
				</tr>
				<tr>
					<th>Designer</th>
					<td> {{$aurora->owner }}</td>
				</tr>
				<tr>
					<th>Date</th>
					<td> {{date( 'M d, Y',strtotime($aurora->created_at)) }}</td>
				</tr>
			</table>
		</div>
			@if($aurora->hasMeta('primaryImages-'.$design->design_id))
				<img alt="Image" class="img img-responsive"
				     src="{{$aurora->getMeta('primaryImages-'.$design->design_id) ?? asset('solar-home.jpg')}}" style="width:100%"/>
		@endif

			<h3>Component Lists</h3>
			<table class="table table-bordered table-striped">
				<thead>
				<tr>
					<th>Manufacturer</th>
					<th style="width: 100% !important">Model</th>
					<th>Quantity</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td>Qcells</td>
					<td>Q PEAK DUO BLK ML-G10 400</td>
					<td>37</td>
				</tr>
				<tr>
					<td>Qcells</td>
					<td>Q PEAK DUO BLK ML-G10 400</td>
					<td>37</td>
				</tr>
				<tr>
					<td>Qcells</td>
					<td>Q PEAK DUO BLK ML-G10 400</td>
					<td>37</td>
				</tr>
				</tbody>
			</table>
	</div>
</div>
	<div class="container-fluid">
		<br>
		<br>
	<div class="row">
		<div class="col-sm-5" style="margin-top:25px">
			@if($aurora->hasMeta('primaryImages-'.$design->design_id))
				<img alt="Image" class="img img-responsive"
				     src="{{$aurora->getMeta('primaryImages-'.$design->design_id) ?? asset('solar-home.jpg')}}" style="width:380px; height:300px"/>

			@endif
		</div>
		<div class="col-sm-1"></div>
		<div class="col-sm-6" style="margin-top:35px">
			<h2> Site Assessment</h2>
			<table class="table table-bordered table-striped">
				<tr>
					<th>Customer</th>
					<td> {{$aurora->first_name . ' ' . $aurora->last_name}}</td>
				</tr>
				<tr>
					<th>Address</th>
					<td> @if(!empty($aurora->location))
							{{$aurora->location }} <br>
							{{$aurora->state . ' , ' . $aurora->city . ' , '. $aurora->zip }}
						@else
							{{$aurora->full_address}}
						@endif
					</td>
				</tr>
			</table>
		</div>
	</div>
<br>


		@if($aurora->hasMeta('primaryImages-'.$design->design_id))
			<img alt="Image" class="img img-responsive"
			     src="{{$aurora->getMeta('primaryImages-'.$design->design_id) ?? asset('solar-home.jpg')}}" style="width:100%">
		@endif

		<br>
		<strong>Notes:</strong>
		<br>
		<br>
		<br>
	<center>	_______________________________ END  ______________________</center>
</div>