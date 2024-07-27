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
		{{--<div class="col-xs-12">
			<img src="{{ asset('assets/'.option('logo'))}}" class="center" style="
			 filter: grayscale(1);
    display: block;
    margin-left: auto;
    margin-right: auto;
    width: 60%;">
		</div>--}}
		<div class="col-xs-12">
			<p class="text-center" style="font-size: 16px; font-weight: 600; margin: 2px;">
				<strong style="font-size: 20px"> فاتورة ضریبة مبسطة</strong><br>
				{{option('title')}} <br>
				{{option('address')}} <br>
				{{option('vat_number')}} <br>
				{{option('mobile')}}
			</p>

		</div>
	</div>


	<div class="row" style="    font-weight: 600;
    font-size: 11px;
    text-align: right; margin: 0px 2px;">
		<div class="col-xs-8">
			{{$order->id}}
		</div>
		<div class="col-xs-4">
			: رقم الفتورۃ
		</div>

		<div class="col-xs-8">
			{{date('d/m/Y h:i:s A', strtotime($order->created_at))}}
		</div>
		<div class="col-xs-4">
			: التاریخ والوقت
		</div>
	</div>
	<div class="row" style="font-weight: 600;
    font-size: 11px;
    text-align: right; margin: 0px 2px;">
		<div class="col-xs-8">
			{{($order->customer && $order->customer->id !== 1) ? $order->customer->full_name : $order->customer_name}}
		</div>
		<div class="col-xs-4">
			: العمیل
		</div>


	</div>


	<div class="row" style="font-size: 13px; line-height: 1.7; font-weight: 600; margin: 0px 3px;">

		<table>
			<thead>
			<tr style="line-height: normal; padding: 1px 0px">
				<th style="width: 8%; text-align: center">الکمیۃ</th>
				<th style="width: 45%; text-align: center">الوصف</th>
				<th style="width: 15%; text-align: center">السعر</th>
				<th style="width: 15%; text-align: center">الضریبۃ</th>
				<th style="width: 17%; text-align: center">اِجمالی شامل الضریبۃ</th>

			</tr>
			</thead>

			<tbody>
			@php( $total_vat = 0)
			@php($total_qty = 0)
			@foreach($order->items as $key => $item)

				<tr>
					<td style="width: 8%; text-align: center"> {{$item->stock}} </td>
					<td style="width: 45%; text-align: center; font-size: 13px">
						{{$item->product->arabic_name ?? $item->product->name}}
					</td>
					<td style="width: 15%; text-align: center">{{sprintf('%0.2f',$item->sale_price) }}</td>
					<td style="width: 15%; text-align: center">{{calculateVat( $item->sale_price * $item->stock )}}</td>
					<td style="width: 17%; text-align: center">{{sprintf('%0.2f',$item->taxable_price * $item->stock)}}</td>

				</tr>
				@php( $total_qty += $item->stock)
				@php(	$total_vat += calculateVat( $item->sale_price * $item->stock ))

			@endforeach
			</tbody>
		</table>
		<br>
		<div class="row" style="font-weight: 600; font-size: 12px; text-align: center">
			<div class="col-xs-4">
				Total
			</div>

			<div class="col-xs-4">
				الاجمالی
			</div>
			<div class="col-xs-4">
				{{sprintf('%0.2f',$order->sub_total)}}
			</div>
			<div class="col-xs-4">
				Discount
			</div>

			<div class="col-xs-4">
				الخصم
			</div>
			<div class="col-xs-4">
				{{$order->discount}}   </div>
			<div class="col-xs-4">
				Total &
			</div>

			<div class="col-xs-4">
				بعد الخصم
			</div>
			<div class="col-xs-4">
				{{sprintf('%0.2f',$order->sub_total)}}
			</div>
			<div class="col-xs-4">
				VAT {{$order->vat}}%
			</div>

			<div class="col-xs-4">
				الضریبۃ
			</div>
			<div class="col-xs-4">
				{{sprintf('%0.2f',$total_vat)}}
			</div>
			<div class="col-xs-3">
				Net
			</div>

			<div class="col-xs-5" style="font-size: 12px;">
				الإجمالي شامل الضريبة
			</div>
			<div class="col-xs-4" style="font-weight: 600; font-size: 16px; text-align: center">
				{{sprintf('%0.2f',$order->total)}}
			</div>
		</div>

	{{--	<p style="font-size: 12px; font-weight: 600; margin: 2px;">
			Cashier: {{$order->cashier_name}}
		</p>--}}

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