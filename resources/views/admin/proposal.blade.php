<style>

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
        size: A4 landscape;
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
            size: A4 landscape;
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
	<img class="background-img" src="{{asset('solar-home.jpg')}}" width="100%"/>
	<div class="box">
		<p style="padding-top: 30%;"></p>
		<img src="{{asset('assets/emp-logo1.png')}}" style="width: 50%;"/>
		<div style="border: 1px solid;    margin: 20% 10% 2% 10%;">
		</div>
		<h6>ENERGY SAVINGS REPORT FOR</h6>
		<div style="border: 1px solid;     margin: 0 35% 5% 35%;">
		</div>
		<h1>{{ucwords($aurora->customer_one_fname . ' ' . $aurora->customer_one_lname)}}</h1>
		<p class="details">{{$aurora->info->full_address}}</p>
		<p class="details">{{phoneFormat($aurora->mobile_phone)}}</p>
		<p>{{$aurora->personal_email}}</p>
		<br>
	</div>
	<br>
	<small class="text-center">This is not a bid for work, but an estimated calculator for the work order that will be
		provided by the
		installation Contractor,
		Disclaimer and disclosure are located in the finance and last page. </small>
</div>
<div class="container-fluid row">
	<div class="col-md-4" style="background-color: #3EBDFF; color: white; height: 100%; text-align:center">
		<h3 style="margin: 30px; padding-top: 30px; font-weight: 400;">UTILITY PRICES HAVE STEADILY
			<strong class="stronger">INCREASED.</strong></h3>

		<div style="border: 1px solid;margin: 0 35% 5% 35%;"></div>
		<h3 style="margin: 20px; padding-top: 5px; font-weight: 400;">
			<strong class="stronger">SINCE 2003</strong>
			NATIONAL AVERAGE
			<strong>UTILITY PRICES</strong>
			HAVE NEARLY
			<strong class="stronger"> DOUBLED.</strong>
		</h3>
		<img class="img img-responsive" src="{{asset('solar-home.jpg')}}" height="200px">
		<br>
	</div>
	<div class="col-md-8">
		<div style="    background-color: #000000;
    color: white;
    text-align: center;
    margin: 0px -10px;
    padding: 12px;
    width: 105%;">
			<h1> YOUR SOLAR DESIGN</h1>
		</div>

		<div style="
    text-align: center;
    margin: 0px -10px;
    width: 105%;">
			<img class="img img-responsive" src="{{asset('solar-home.jpg')}}" width="100%" height="500px">
		</div>
		<div class="row" style=" text-align: center;
    margin: 0px -10px;
    width: 105%;">
			<div class="col-md-6 small-div">
				SYSTEM SIZE
				<br>
				<strong>{{$data['system_size'] ? round($data['system_size'] /1000, 2). ' kWh': ''}}</strong>
			</div>
			<div class="col-md-6 small-div">
				ESTIMATED YEARLY PRODUCTION
				<br>
				<strong>{{$data['energy_production']['annual'] ? round($data['energy_production']['annual'],2) . ' kWh' : ''}}</strong>
			</div>
			<div class="col-md-6 small-div">
				MODULES<br>
				<strong>Hanwha Q.PEAK DUO BLK ML-G10 400 (x46)</strong>
			</div>
			<div class="col-md-6 small-div">
				INVERTER<br>
				<strong> SolarEdge SE7600A-US (x2)</strong>
			</div>
		</div>
	</div>
</div>
<div class="container-fluid">
	<img class="img" src="{{asset('laptop.jpg')}}" style="    height: 100%;
    width: 100%;
    left: 0px;
    right: 0px;
    position: absolute;
    z-index: -1;"/>
	<h1 class="steps">THE PROCESS</h1>
	<br>
	<h5 class="steps">STEP 1</h5>
	<h4 class="steps" style="
    padding-top: 0px;">Savings Report</h4>
	<h5 class="steps">STEP 2</h5>
	<h4 class="steps" style="
    padding-top: 0px;">Approval Process</h4>
	<h5 class="steps">STEP 3</h5>
	<h4 class="steps" style="
    padding-top: 0px;">Documents</h4>
	<h5 class="steps">STEP 4</h5>
	<h4 class="steps" style="
    padding-top: 0px;">Site Survey</h4>
	<h5 class="steps">STEP 5</h5>
	<h4 class="steps" style="
    padding-top: 0px;">CAD/Permit</h4>
	<h5 class="steps">STEP 6</h5>
	<h4 class="steps" style="
    padding-top: 0px;">Installation</h4>
	<h5 class="steps">STEP 7</h5>
	<h4 class="steps" style="
    padding-top: 0px;">System Activation</h4>
	<br>
	<br>
</div>
@if($consumptions)
	<div class="container-fluid">

		<img class="img" src="{{asset('family.jpg')}}" style="    height: 100%;
    width: 100%;
    left: 0px;
    right: 0px;
    position: absolute;
    z-index: -1;"/>
		<h1 class="text-center">PROPOSAL DETAILS</h1>
		<div style="
        margin-top: 80px;
        width: 50%;
        border-radius: 3%;
        margin-left: 23%;
        opacity: 0.9;">
			<strong class="text-left">Utility</strong>
			<hr>
			<table class="table" style="border-bottom: white;
    color: black;">
				<tr>
					<td class="text-left">Annual Utility Bill</td>
					<td class="text-right">${{round(array_sum($consumptions['monthly_bill']), 2)}}</td>
				</tr>
				<tr>
					<td class="text-left">Current Consumption</td>
					<td class="text-right">{{round(array_sum($consumptions['monthly_energy']), 2) . ' kWh'}}</td>
				</tr>
				<tr>
					<td class="text-left">Estimated Cost Per KWh
					</td>
					<td class="text-right">
						${{round(array_sum($consumptions['monthly_bill']), 2) / round(array_sum($consumptions['monthly_energy']), 2) . '/kWh'}}</td>
				</tr>
			</table>
			<strong class="text-left">System</strong>
			<hr>
			<table class="table" style="border-bottom: white;
    color: black;">
				<tr>
					<td class="text-left">System Size</td>
					<td class="text-right">{{$data['system_size'] ? round($data['system_size'] /1000, 2). ' kWh':''}}</td>
				</tr>
				<tr>
					<td class="text-left">Year 1 Solar Production</td>
					<td
						class="text-right">{{$data['energy_production']['annual'] ? round($data['energy_production']['annual'],2) . ' kWh' : ''}}</td>
				</tr>
			</table>

			<strong class="text-left">Cost</strong>
			<hr>
			<table class="table" style="border-bottom: white;
    color: black;">
				<tr>
					<td class="text-left">Total Loan Amount</td>
					<td class="text-right"></td>
				</tr>
			</table>

		</div>

	</div>

@endif


