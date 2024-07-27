@extends('admin.layouts.admin')

@section('page-title','Invoice')
@section('heading','Invoice')
@section('breadcrumbs', 'Invoice')

@section('content')
	<div class="card">
		<div class="card-header bg-transparent header-elements-inline">
			<h6 class="card-title">Customer Invoice</h6>
			<div class="header-elements">
				<button type="button" class="btn btn-light btn-sm ml-3"><i class="icon-printer mr-2"></i> Print</button>
			</div>
		</div>

		<div class="card-body">
			<div class="row">
				<div class="col-sm-6">
					<div class="mb-4">
						<img src="{{asset('assets/emp-logo.png')}}">
					</div>
				</div>

				<div class="col-sm-6">
					<div class="mb-4">
						<div class="text-sm-right">
							<h4 class="text-primary mb-2 mt-md-2">Invoice #{{$order->invoice_no}}</h4>
							<ul class="list list-unstyled mb-0">
								<li>Date: <span class="font-weight-semibold">{{$order->date}}</span></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="d-md-flex flex-md-wrap">
				<div class="mb-4 mb-md-2">
					<span class="text-muted">Invoice To:</span>
					<ul class="list list-unstyled mb-0">
						<li><h5 class="my-2">{{$order->customer->full_name}}</h5></li>
						<li><span class="font-weight-semibold">{{$order->customer->shipping_address}}</span></li>
						<li>{{$order->customer->shipping_city}}, {{$order->customer->shipping_state}}
							, {{$order->customer->shipping_zipcode}}</li>
						<li><a href="#">{{$order->customer->email}}</a></li>
					</ul>
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
						<td>{{$item->sale_price}}</td>
						<td>{{$item->sale_price * $item->stock}}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		</div>

		<div class="card-body">
			<div class="d-md-flex flex-md-wrap">
				<div class="pt-2 mb-3 wmin-md-400 ml-auto">
					<div class="table-responsive">
						<table class="table">
							<tbody>
							<tr>
								<th>Subtotal:</th>
								<td class="text-right">{{$order->sub_total}}</td>
							</tr>
							<tr>
								<th>Delivery:</th>

								<td class="text-right">{{$order->delivery_charges}}</td>
							</tr>
							<tr>
								<th>Discount:</th>

								<td class="text-right">-{{$order->discount}}</td>
							</tr>
							<tr>
								<th>Total:</th>
								<td class="text-right text-primary"><h5 class="font-weight-semibold">{{$order->total}}</h5></td>
							</tr>
							</tbody>
						</table>
					</div>


				</div>
			</div>
		</div>

		<div class="card-footer">
			<span class="text-muted">Thank you for Shopping...</span>
		</div>
	</div>
@endsection
