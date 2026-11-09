@extends('admin.layouts.admin')

@section('page-title','Orders')
@section('heading','Orders')
@section('breadcrumbs', 'Orders')

@php
	$range = $f['range'];
	$money = fn( $v ) => 'Rs ' . number_format( (float) $v );
@endphp

@section('content')
<div class="rk">

	@include('admin.partials.report-head', [
		'eyebrow' => 'Orders',
		'range'   => $range,
		'presets' => $presets,
		'params'  => [ 'cashier' => $f['cashier'], 'q' => $f['search'], 'show' => $f['show'] ],
		'note'    => implode( ' · ', array_filter( [
			'By sale date',
			$f['cashier'] !== '' ? 'cashier ' . ( $f['cashier'] === '-' ? '(no name)' : $f['cashier'] ) : null,
			$f['search'] !== '' ? '“' . $f['search'] . '”' : null,
			$f['show'] !== '' ? strtolower( $show[ $f['show'] ] ) : null,
		] ) ),
	])

	<form method="get" class="rk-filters" id="order-filters">
		<div class="rk-field rk-field--range">
			<label for="f-range">Sale date</label>
			<input type="text" name="range" id="f-range" value="{{ $range->value() }}" class="form-control rk-range" autocomplete="off">
		</div>
		<div class="rk-field">
			<label for="f-cashier">Cashier</label>
			<select name="cashier" id="f-cashier" class="form-control">
				<option value="">All cashiers</option>
				@foreach($cashiers as $name => $count)
					<option value="{{ $name }}" {{ mb_strtoupper( $f['cashier'] ) === $name ? 'selected' : '' }}>{{ $name }}</option>
				@endforeach
				<option value="-" {{ $f['cashier'] === '-' ? 'selected' : '' }}>(No cashier name)</option>
			</select>
		</div>
		<div class="rk-field">
			<label for="f-show">Show</label>
			<select name="show" id="f-show" class="form-control">
				@foreach($show as $value => $label)
					<option value="{{ $value }}" {{ $f['show'] === $value ? 'selected' : '' }}>{{ $label }}</option>
				@endforeach
			</select>
		</div>
		<div class="rk-field rk-field--grow">
			<label for="f-q">Order # or customer</label>
			<input type="search" name="q" id="f-q" value="{{ $f['search'] }}" class="form-control" placeholder="e.g. 21825 or Mohsin">
		</div>
		<div class="rk-actions">
			<button type="submit" class="btn btn-primary">Apply</button>
			<a href="{{ url()->current() }}" class="btn btn-light">Reset</a>
		</div>
	</form>

	<div class="rk-kpis" style="--rk-tiles: 7">
		<div class="rk-kpi rk-kpi--lead">
			<div class="rk-kpi__label">Net sales</div>
			<div class="rk-kpi__value">{{ $money( $summary['net_sales'] ) }}</div>
		</div>
		<div class="rk-kpi">
			<div class="rk-kpi__label">Orders</div>
			<div class="rk-kpi__value">{{ number_format( $summary['orders'] ) }}</div>
		</div>
		<div class="rk-kpi">
			<div class="rk-kpi__label">Average order</div>
			<div class="rk-kpi__value">{{ $money( $summary['avg_order'] ) }}</div>
		</div>
		<div class="rk-kpi">
			<div class="rk-kpi__label">Items sold</div>
			<div class="rk-kpi__value">{{ number_format( $summary['items'] ) }}</div>
		</div>
		<div class="rk-kpi">
			<div class="rk-kpi__label">Discounts</div>
			<div class="rk-kpi__value">{{ $money( $summary['discount'] ) }}</div>
		</div>
		<div class="rk-kpi">
			<div class="rk-kpi__label">Delivery</div>
			<div class="rk-kpi__value">{{ $money( $summary['delivery'] ) }}</div>
		</div>
		<div class="rk-kpi {{ $summary['unpaid'] > 0 ? 'rk-kpi--warn' : '' }}">
			<div class="rk-kpi__label">Unpaid</div>
			<div class="rk-kpi__value">{{ $money( $summary['unpaid'] ) }}</div>
		</div>
	</div>

	<div class="rk-card">
		<div class="rk-card__head">
			<h3>{{ number_format( $summary['orders'] ) }} {{ $summary['orders'] === 1 ? 'order' : 'orders' }}</h3>
			<span class="rk-muted">Newest first · net = item sales − discount + delivery · click an order # to open its invoice</span>
		</div>
		<div class="table-responsive">
			<table id="orderTable" class="table table-hover rk-table">
				<thead>
				<tr>
					<th>Order #</th>
					<th>Date</th>
					<th>Customer</th>
					<th>Cashier</th>
					<th class="num">Items</th>
					<th class="num">Item sales</th>
					<th class="num">Discount</th>
					<th class="num">Delivery</th>
					<th class="num">Net total</th>
					<th class="num">Unpaid</th>
					<th></th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<th colspan="4">Total ({{ number_format( $summary['orders'] ) }} orders)</th>
					<th class="num">{{ number_format( $summary['items'] ) }}</th>
					<th class="num">{{ number_format( $summary['item_sales'] ) }}</th>
					<th class="num">{{ number_format( $summary['discount'], 2 ) }}</th>
					<th class="num">{{ number_format( $summary['delivery'] ) }}</th>
					<th class="num">{{ number_format( $summary['net_sales'] ) }}</th>
					<th class="num {{ $summary['unpaid'] > 0 ? 'rk-bad' : '' }}">{{ number_format( $summary['unpaid'] ) }}</th>
					<th></th>
				</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>
@endsection

@include('admin.partials.report-kit')
@include('plugins.ajax')
@include('plugins.DataTables')

@push('footer')
	<script>
		$(function () {
			const num = (d, t) => t === 'display' ? Number(d || 0).toLocaleString('en-PK', {maximumFractionDigits: 2}) : d
			const unpaid = (d, t) => t === 'display' && Number(d) > 0 ? '<span class="rk-bad">' + num(d, t) + '</span>' : num(d, t)

			$('#orderTable').DataTable({
				processing: true,
				serverSide: true,
				ordering: false,
				autoWidth: false,
				searching: false,
				pageLength: 100,
				lengthMenu: [[50, 100, 500, 1000], [50, 100, 500, 1000]],
				language: {emptyTable: 'No orders match these filters.', processing: 'Loading…'},
				ajax: {
					url: "{{ route('reports.order.ajax') }}",
					type: 'post',
					data: {
						range: @json($range->value()),
						cashier: @json($f['cashier']),
						q: @json($f['search']),
						show: @json($f['show'])
					}
				},
				columns: [
					{data: 'id'},
					{data: 'when'},
					{data: 'customer_name'},
					{data: 'cashier_name'},
					{data: 'items', className: 'num', render: num},
					{data: 'sub_total', className: 'num', render: num},
					{data: 'discount_amount', className: 'num'},
					{data: 'delivery_charges', className: 'num', render: num},
					{data: 'total', className: 'num', render: (d, t) => t === 'display' ? '<b>' + num(d, t) + '</b>' : d},
					{data: 'debit', className: 'num', render: unpaid},
					{data: 'action', className: 'text-nowrap'}
				]
			})

			// Picking a cashier or "show" applies straight away.
			$('#f-cashier, #f-show').on('change', function () { $('#order-filters').trigger('submit') })
		})
	</script>
@endpush
