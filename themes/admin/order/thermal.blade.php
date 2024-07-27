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
	<div class="row">
		<div class="col-md-5">
			<img src="{{ asset('assets/'.option('logo'))}}" class="center" style="
			 filter: grayscale(1);
    display: block;
    margin-left: auto;
    margin-right: auto;
    width: 60%;">
		</div>
		<div class="col-md-7">
			<p class="text-center" style="font-size: 16px; font-weight: 600; margin: 2px;">{{option('title')}}</p>
			<p class="text-center" style="font-size: 12px; font-weight: 600; margin: 2px;">SIMPLIFIED VAT INVOICE</p>
			<div class="row text-center">
				<strong>
					فاتورة ضریبة مبسطة
				</strong>
			</div>
		</div>
	</div>

	<p class="text-center" style="font-size: 14px; font-weight: 600;  margin: 2px;">{{option('address')}}</p>
	<p style="font-size: 12px; font-weight: 600;  margin: 2px;"><b>Tel:</b> {{option('mobile')}}</p>
	<p style="font-size: 12px; font-weight: 600;  margin: 2px;">VAT No: {{option('vat_number')}}</p>
	<p style="font-size: 10px; font-weight: 600; margin: 2px;">
		Inv # - Date & Time: {{$order->id}} - {{date('d/m/Y h:i:s A', strtotime($order->created_at))}}
	</p>
	<p style="font-size: 12px; font-weight: 600; margin: 2px;">
		Customer: {{($order->customer && $order->customer->id !== 1) ? $order->customer->full_name : $order->customer_name}}
	</p>

	<div class="row" style="font-size: 12px; line-height: 2; margin: 0px 3px;">

		<table>
			<thead>
			<tr>
				<th style="width: 60%">Item</th>
				<th style="width: 15%">Qty</th>
				<th style="width: 15%">Rate</th>
				<th style="width: 10%">Total</th>
			</tr>
			</thead>

			<tbody>
			@php( $total_vat = 0)
			@php($total_qty = 0)
			@foreach($order->items as $key => $item)

				<tr>
					<td style="float: left !important; text-align: left !important;">
						@if($item->product->name)
							{{$item->product->name}}
						@endif
						<br>
						@if($item->product->arabic_name)
							<b>  {{$item->product->arabic_name}}
							</b>
						@endif

					</td>
					<td> {{$item->stock}} </td>
					<td>{{sprintf('%0.2f',$item->taxable_price) }}</td>
					<td>{{sprintf('%0.2f',$item->taxable_price * $item->stock)}}</td>
				</tr>
				@php( $total_qty += $item->stock)
				@php(	$total_vat += calculateVat( $item->sale_price * $item->stock ))

			@endforeach
			{{-- <tr>
				 <td class="text-left" colspan="3">Total Qty <strong>اجمالی کمیۃ </strong></td>
				 <td  colspan="2">
					 <strong>{{$total_qty}}</strong></td>
			 </tr>--}}
			<tr>
				<td class="text-left" colspan="3"> Ex. VAT <strong>بدون قیمۃ المضافۃ</strong></td>
				<td colspan="2">
					<strong>{{sprintf('%0.2f',$order->sub_total)}}</strong></td>
			</tr>

			<tr>
				<td class="text-left" colspan="3">Vat Amount ({{$order->vat}} %)<strong> قیمۃ المضافۃ </strong></td>
				<td colspan="2"><strong> {{sprintf('%0.2f',$total_vat)}}</strong></td>
			</tr>
			<tr>
				<td class="text-left" colspan="3">Discount <strong> خصم</strong></td>
				<td colspan="2" class="text-center"><strong> {{$order->discount}}</strong></td>
			</tr>
			<tr>
				<td class="text-left" colspan="3"><strong> Net Amount اجمالی</strong></td>
				<td colspan="2"><strong>{{sprintf('%0.2f',$order->total)}}</strong></td>
			</tr>

			</tbody>
		</table>
		<p style="font-size: 12px; font-weight: 600; margin: 2px;">
			Cashier: {{$order->cashier_name}}
		</p>
		<hr style="margin-top: 1px;
     margin-bottom: 1px;">
		<hr style="margin-top: 1px;
     margin-bottom: 1px;">
		<h5 class="text-center"><b>THANK YOU FOR VISIT</b></h5>
		<hr style="margin-top: 1px;
     margin-bottom: 1px;">
		<hr style="margin-top: 1px;
     margin-bottom: 1px;">
   <?php

   /* $date = date( 'm/d/Y h:i:s A', strtotime( $order->created_at ) );
	$data = "اسم المؤسسۃ:" . option( 'title' ) . "    ";
	$data .= "العنوان :" . option( 'address' ) . "    ";
	$data .= "الرقم الضربی :" . option( 'vat_number' ) . "    ";
	$data .= "رقم الفاتورۃ :" . $order->id . "    ";
	$data .= "التاریخ و الوقت  :" . $date . "    ";
	$data .= "المبلغ  :" . sprintf( '%0.2f', $order->sub_total ) . "    ";
	$data .= "القیمۃ المضافۃ :" . sprintf( '%0.2f', $total_vat ) . "    ";
	$data .= "الاِجمالی :" . sprintf( '%0.2f', $order->total );
	// data:image/png;base64, .........*/
   $displayQRCodeAsBase64 = \Salla\ZATCA\GenerateQrCode::fromArray( [
     new \Salla\ZATCA\Tags\Seller( option( 'title' ) ), // seller name
     new \Salla\ZATCA\Tags\TaxNumber( option( 'vat_number' ) ), // seller tax number
     new \Salla\ZATCA\Tags\InvoiceDate( $order->created_at ), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
     new \Salla\ZATCA\Tags\InvoiceTotalAmount( $order->sub_total ), // invoice total amount
     new \Salla\ZATCA\Tags\InvoiceTaxAmount( $total_vat ) // invoice tax amount
   ] )->render();

   // now you can inject the output to src of html img tag :)
   // <img src="$displayQRCodeAsBase64" alt="QR Code" />
   ?>
		<img class="center" style="width: 60%" src="{{$displayQRCodeAsBase64}}" alt="QR Code"/>
		<br><br><br>
	</div>

</div>
<script src="{{asset('themes/default/jquery/jquery.min.js')}}"></script>
<script src="{{asset('themes/default/bootstrap-3/js/bootstrap.min.js')}}"></script>

<script type="text/javascript">
	//--kiosk-printing
	'use strict'

	window.print()
	var beforePrint = function () {
		console.log('Functionality to run before printing.')
	}

	var afterPrint = function () {
		console.log('Functionality to run after printing')
		window.history.back()
	}

	if (window.matchMedia) {
		var mediaQueryList = window.matchMedia('print')
		mediaQueryList.addListener(function (mql) {
			if (mql.matches) {
				beforePrint()
			} else {
				afterPrint()
			}
		})
	}

	window.onbeforeprint = beforePrint
	window.onafterprint = afterPrint


</script>
</body>

</html>