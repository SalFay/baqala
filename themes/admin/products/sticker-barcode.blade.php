<!DOCTYPE html>
<html lang="ar">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

	<link href="{{asset('themes/default/bootstrap-3/css/bootstrap.min.css')}}" rel="stylesheet" id="bootstrap-css">

	<title>{{option('title')}}</title>
	<style>


     strong {
         font-size: 18px;
     }


     .center {
         display: block;
         margin-left: auto;
         margin-right: auto;
         width: 100%;
     }

     .well {
         color: #000;
         background-color: #ffffff;
         border: 1px solid #e3e3e3;
         border-radius: 4px;
         box-shadow: 0 0 1in -0.25in rgba(0, 0, 0, 0.5);
         /*
										padding: 2mm 5mm;
				*/
         margin: 0 auto;
         width: 3.5in;
     }

     table tr {
         border-bottom: 1px dotted !important;
         border-top: 1px dotted !important;

     }

     @media print {

         .col-sm-5 {
             flex: 0 0 auto;
             width: 40% !important;
         }

         .col-sm-7 {
             flex: 0 0 auto;
             width: 60% !important;
         }

         @page {
             margin: 0;
             sheet-size: 3.5in 280px; /* imprtant to set paper size */

         }

         html, body {
             padding: 0;
         }

         #printContainer {
             width: 3in;
             margin: auto;
             padding: 5px;
             /*border: 2px dotted #000;*/
             text-align: justify;
         }

         .text-center {
             text-align: center;
         }
     }
	</style>
</head>
<body style="line-height: 1.3">
<div id='printContainer' class=" well">

	@for($i=0; $i<=5; $i++)
		<hr>
		<div style=" line-height: 1; margin: 0px 3px;">
			<h5 class="card-title text-center" style="font-size: 18px;">{{$product->arabic_name ?? $product->name}}</h5>
			<img class="center" style="width: 60%"
			     src="data:image/png;base64,{{\Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($product->pid, 'C128')}}"
			     alt="barcode"/>
			<h5 class="text-center"> {{$product->pid}}</h5>
		</div>




	@endfor
</div>
</body>
</html>
