@extends('admin.layouts.admin')

@section('page-title','Inventory Log')
@section('heading','Inventory Log')
@section('breadcrumbs', 'Inventory Log')

@php
	$range = \App\Support\DateRange::parse( request( 'range' ), 'month' );
	$kind = in_array( request( 'kind' ), [ 'order', 'stock' ], true ) ? request( 'kind' ) : '';
	$status = in_array( request( 'status' ), [ 'Available', 'Sold' ], true ) ? request( 'status' ) : '';
@endphp

@section('content')
<div class="rk">

	@include('admin.partials.report-head', [
		'eyebrow' => 'Inventory log',
		'range'   => $range,
		'presets' => \App\Support\DateRange::presets(),
		'params'  => [ 'kind' => $kind, 'status' => $status ],
		'note'    => 'Every stock movement: items sold on orders and stock received from vendors',
	])

	<form method="get" class="rk-filters" id="log-filters">
		<div class="rk-field rk-field--range">
			<label for="f-range">Date</label>
			<input type="text" name="range" id="f-range" value="{{ $range->value() }}" class="form-control rk-range" autocomplete="off">
		</div>
		<div class="rk-field">
			<label for="f-kind">Type</label>
			<select name="kind" id="f-kind" class="form-control">
				<option value="">Orders and stock</option>
				<option value="order" {{ $kind === 'order' ? 'selected' : '' }}>Orders (sold)</option>
				<option value="stock" {{ $kind === 'stock' ? 'selected' : '' }}>Stock received</option>
			</select>
		</div>
		<div class="rk-field">
			<label for="f-status">Status</label>
			<select name="status" id="f-status" class="form-control">
				<option value="">Any status</option>
				<option value="Sold" {{ $status === 'Sold' ? 'selected' : '' }}>Sold</option>
				<option value="Available" {{ $status === 'Available' ? 'selected' : '' }}>Available</option>
			</select>
		</div>
		<div class="rk-actions">
			<button type="submit" class="btn btn-primary">Apply</button>
			<a href="{{ url()->current() }}" class="btn btn-light">Reset</a>
		</div>
	</form>

	<div class="rk-card">
		<div class="rk-card__head">
			<h3 id="log-count">Movements</h3>
			<span class="rk-muted" id="log-qty"></span>
		</div>
		<div class="table-responsive">
			<table id="inventoryTable" class="table table-hover rk-table">
				<thead>
				<tr>
					<th>Order #</th>
					<th>Type</th>
					<th>Product</th>
					<th class="num">Qty</th>
					<th class="num">Price</th>
					<th>Status</th>
					<th>Date</th>
				</tr>
				</thead>
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
			$('#inventoryTable').DataTable({
				processing: true,
				serverSide: true,
				ordering: false,
				autoWidth: false,
				pageLength: 100,
				lengthMenu: [[50, 100, 500, 1000], [50, 100, 500, 1000]],
				language: {emptyTable: 'No stock movements match these filters.', processing: 'Loading…', search: 'Search:'},
				ajax: {
					url: "{{ route('reports.inventory.ajax') }}",
					type: 'post',
					data: {date: @json($range->value()), kind: @json($kind), status: @json($status)},
					dataSrc: function (json) {
						$('#log-count').text(Number(json.recordsFiltered || 0).toLocaleString('en-PK') + ' movements')
						$('#log-qty').text(Number(json.qty || 0).toLocaleString('en-PK') + ' items in total · newest first · search covers product, order # and status')
						return json.data
					}
				},
				columns: [
					{data: 'id'},
					{data: 'type'},
					{data: 'product'},
					{data: 'stock', className: 'num'},
					{data: 'cost', className: 'num'},
					{data: 'status'},
					{data: 'date', className: 'text-nowrap'}
				]
			})
			$('#f-kind, #f-status').on('change', function () { $('#log-filters').trigger('submit') })
		})
	</script>
@endpush
