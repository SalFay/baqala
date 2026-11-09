@extends('admin.layouts.admin')

@section('page-title','Margin / Profit')
@section('heading','Margin / Profit')
@section('breadcrumbs', 'Margin / Profit')

@php
	$range = $f['range'];
	$money = fn( $v ) => 'Rs ' . number_format( (float) $v );
	$s = $summary;
	$hasCost = $s['profit'] !== null;
@endphp

@section('content')
<div class="rk">

	@include('admin.partials.report-head', [
		'eyebrow' => 'Margin / Profit',
		'range'   => $range,
		'presets' => $presets,
		'params'  => [ 'category' => $f['category'], 'q' => $f['search'] ],
		'note'    => implode( ' · ', array_filter( [
			'Every item sold, by sale date',
			$f['category'] ? ( $categories[ $f['category'] ] ?? null ) : null,
			$f['search'] !== '' ? '“' . $f['search'] . '”' : null,
		] ) ),
	])

	<form method="get" class="rk-filters" id="line-filters">
		<div class="rk-field rk-field--range">
			<label for="f-range">Sale date</label>
			<input type="text" name="range" id="f-range" value="{{ $range->value() }}" class="form-control rk-range" autocomplete="off">
		</div>
		<div class="rk-field">
			<label for="f-category">Category</label>
			<select name="category" id="f-category" class="form-control">
				<option value="">All categories</option>
				@foreach($categories as $id => $name)
					<option value="{{ $id }}" {{ $f['category'] === (int) $id ? 'selected' : '' }}>{{ $name }}</option>
				@endforeach
			</select>
		</div>
		<div class="rk-field rk-field--grow">
			<label for="f-q">Product or order #</label>
			<input type="search" name="q" id="f-q" value="{{ $f['search'] }}" class="form-control" placeholder="e.g. Pizza or 21825">
		</div>
		<div class="rk-actions">
			<button type="submit" class="btn btn-primary">Apply</button>
			<a href="{{ url()->current() }}" class="btn btn-light">Reset</a>
		</div>
	</form>

	<div class="rk-kpis" style="--rk-tiles: 6">
		<div class="rk-kpi rk-kpi--lead">
			<div class="rk-kpi__label">Net sales</div>
			<div class="rk-kpi__value">{{ $money( $s['net'] ) }}</div>
		</div>
		<div class="rk-kpi">
			<div class="rk-kpi__label">Item sales</div>
			<div class="rk-kpi__value">{{ $money( $s['sales'] ) }}</div>
		</div>
		<div class="rk-kpi">
			<div class="rk-kpi__label">Discounts</div>
			<div class="rk-kpi__value">{{ $money( $s['discount'] ) }}</div>
		</div>
		<div class="rk-kpi">
			<div class="rk-kpi__label">Items sold</div>
			<div class="rk-kpi__value">{{ number_format( $s['items'] ) }}</div>
		</div>
		<div class="rk-kpi">
			<div class="rk-kpi__label">Profit</div>
			<div class="rk-kpi__value">{{ $hasCost ? $money( $s['profit'] ) : '—' }}</div>
		</div>
		<div class="rk-kpi">
			<div class="rk-kpi__label">Margin</div>
			<div class="rk-kpi__value">{{ $s['margin'] !== null ? number_format( $s['margin'] * 100, 1 ) . '%' : '—' }}</div>
		</div>
	</div>

	@if($s['lines'] > 0 && $s['coverage'] < 0.999)
		<div class="rk-notice">
			@if(!$hasCost)
				<b>Profit can't be worked out yet.</b>
				None of these sales has a real cost: each one was saved with its cost equal to its sale price, and
				products have no purchase price set. Profit will show here for sales saved with a real cost
				(a purchase price above 0 and below the sale price).
			@else
				<b>Profit covers {{ number_format( $s['coverage'] * 100, 0 ) }}% of sales.</b>
				Items without a cost price (shown as —) are left out of profit and margin rather than guessed.
			@endif
		</div>
	@endif

	<div class="rk-card">
		<div class="rk-card__head">
			<h3>{{ number_format( $s['lines'] ) }} {{ $s['lines'] === 1 ? 'line' : 'lines' }} in {{ number_format( $s['orders'] ) }} orders</h3>
			<span class="rk-muted">Each order's discount is shared across its items by value</span>
		</div>
		<div class="table-responsive">
			<table id="lineTable" class="table table-hover rk-table">
				<thead>
				<tr>
					<th>Order #</th>
					<th>Date</th>
					<th>Product</th>
					<th class="num">Qty</th>
					<th class="num">Price</th>
					<th class="num">Sales</th>
					<th class="num">Discount</th>
					<th class="num">Net</th>
					<th class="num">Cost</th>
					<th class="num">Profit</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<th colspan="3">Total</th>
					<th class="num">{{ number_format( $s['items'] ) }}</th>
					<th></th>
					<th class="num">{{ number_format( $s['sales'] ) }}</th>
					<th class="num">{{ number_format( $s['discount'], 2 ) }}</th>
					<th class="num">{{ number_format( $s['net'], 2 ) }}</th>
					<th class="num">{{ $hasCost ? '' : '—' }}</th>
					<th class="num">{{ $hasCost ? number_format( $s['profit'], 2 ) : '—' }}</th>
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

			$('#lineTable').DataTable({
				processing: true,
				serverSide: true,
				ordering: false,
				autoWidth: false,
				searching: false,
				pageLength: 100,
				lengthMenu: [[50, 100, 500, 1000], [50, 100, 500, 1000]],
				language: {emptyTable: 'No items sold match these filters.', processing: 'Loading…'},
				ajax: {
					url: "{{ route('reports.order-items.ajax') }}",
					type: 'post',
					data: {range: @json($range->value()), category: @json($f['category'] ?: ''), q: @json($f['search'])}
				},
				columns: [
					{data: 'order_id'},
					{data: 'date', className: 'text-nowrap'},
					{data: 'product'},
					{data: 'stock', className: 'num', render: num},
					{data: 'sale_price', className: 'num', render: num},
					{data: 'line_sales', className: 'num', render: num},
					{data: 'discount_share', className: 'num'},
					{data: 'net', className: 'num', render: (d, t) => t === 'display' ? '<b>' + d + '</b>' : d},
					{data: 'cost', className: 'num'},
					{data: 'profit', className: 'num'}
				]
			})

			$('#f-category').on('change', function () { $('#line-filters').trigger('submit') })
		})
	</script>
@endpush
