@extends('admin.layouts.admin')

@section('page-title','Invoice')
@section('heading','Invoice')
@section('breadcrumbs', 'Invoice')
@push('head')
	<style>
     @media print {

         .card {
             display: block;
         }

         .table-responsive {
             scroll-behavior: unset;
             page-break-after: always;
         }

         .hidden-print {
             display: none;
         }


     }


	</style>
@endpush
@section('content')
	<div class="card">
		<div class="header-elements">
			<button type="button" onclick="window.print()" class="btn btn-light btn-sm hidden-print"><i
					class="icon-printer mr-2"></i> Print
			</button>
		</div>

		<div class="card-body">
			<div class="container row text-center" style="margin-top: 10px;">
				<div class="col-md-3">
					<img class="img-responsive" width="120" src="{{asset('assets').'/'. option('logo')}}">
				</div>
				<div class="col-md-9" style="line-height: 10px;">
					<h4 style="color: darkgreen;font-weight: 700;font-size: 40px;">{{option('title')}} </h4>
					<p style="color: darkblue;font-weight: 600;font-size: 20px;">{{option('address')}}</p>
					<p style="color: darkblue;font-weight: 600;font-size: 20px">Mob: {{option('mobile')}}</p>
				</div>

			</div>
			<div class="container row" style="margin-top: 10px;">

				<div class="col-md-4" style="border: 1px solid black;">
					<h3 style="border-bottom: 1px solid black ">Bill From</h3>
					<ul class="list list-unstyled mb-0">
						<li><h5 class="my-2">{{$order->vendor->name}}</h5></li>
						<li><span class="font-weight-semibold">{{$order->vendor->address}}</span></li>
						<li><a href="#">{{$order->vendor->mobile}}</a></li>
					</ul>
				</div>

				<div class="col-md-8">
					<h1 style="text-align: center;">Invoice</h1>
					<table class="table table-bordered">
						<tr>
							<th>Invoice ID</th>
							<th>Bill</th>
							<th>Date</th>
						</tr>
						<tr>
							<td>{{$order->invoice_no}}</td>
							<td>{{$order->id}}</td>
							<td>{{date('d/m/Y', strtotime($order->date))}}</td>

						</tr>
					</table>
				</div>
			</div>


		</div>


		<div class="table-responsive">
			<table class="table table-lg">
				<thead>
				<tr>
					<th>Description</th>
					<th>Stock</th>
					<th>Price</th>
					<th>Total</th>
				</tr>
				</thead>
				<tbody>
				@foreach($order->items as $item)
					<tr>
						<td>
							<h6 class="mb-0">{{$item->product->name}}</h6>
						</td>
						<td>{{$item->stock}}</td>
						<td>{{$item->cost}}</td>
						<td>{{$item->cost * $item->stock}}</td>
					</tr>
				@endforeach
				</tbody>
			</table>

			<div class="d-md-flex flex-md-wrap">
				<div class="pt-2 mb-3 wmin-md-400 ml-auto">
					<div class="table-responsive">
						<table class="table">
							<tbody>
							<tr>
								<th>Subtotal:</th>
								<td class="text-center">{{$order->sub_total}}</td>
							</tr>
							<tr>
								<th>Delivery:</th>

								<td class="text-center">{{$order->delivery_charges}}</td>
							</tr>
							<tr>
								<th>Discount:</th>

								<td class="text-center">-{{$order->discount}}</td>
							</tr>
							<tr>
								<th>Total:</th>
								<td class="text-center text-primary"><h5 class="font-weight-semibold">{{$order->total}}</h5></td>
							</tr>
							</tbody>
						</table>
					</div>


				</div>
			</div>
			<div class="card-footer">
				<span class="text-muted">Thank you for Shopping...</span>
			</div>
		</div>


	</div>
@endsection
