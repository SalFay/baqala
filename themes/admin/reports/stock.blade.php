@extends('admin.layouts.admin')

@section('page-title','Stock Invoices')
@section('heading','Stock Invoices')
@section('breadcrumbs', 'Stock Invoices')

@php
	$money = fn( $v ) => 'Rs ' . number_format( (float) $v );
	$s = $summary;
	$canView = user_can( 'access vendor invoice' );
@endphp

@section('content')
<div class="rk">

	@include('admin.partials.report-head', [
		'eyebrow' => 'Stock invoices',
		'range'   => $range,
		'presets' => $presets,
		'params'  => [ 'vendor' => $vendor ],
		'note'    => 'Stock bought from vendors, by invoice date' . ( $vendor ? ' · ' . ( $vendors[ $vendor ] ?? '' ) : '' ),
	])

	<form method="get" class="rk-filters" id="stock-filters">
		<div class="rk-field rk-field--range">
			<label for="f-range">Invoice date</label>
			<input type="text" name="range" id="f-range" value="{{ $range->value() }}" class="form-control rk-range" autocomplete="off">
		</div>
		<div class="rk-field rk-field--grow">
			<label for="f-vendor">Vendor</label>
			<select name="vendor" id="f-vendor" class="form-control">
				<option value="">All vendors</option>
				@foreach($vendors as $id => $name)
					<option value="{{ $id }}" {{ $vendor === (int) $id ? 'selected' : '' }}>{{ $name }}</option>
				@endforeach
			</select>
		</div>
		<div class="rk-actions">
			<button type="submit" class="btn btn-primary">Apply</button>
			<a href="{{ url()->current() }}" class="btn btn-light">Reset</a>
		</div>
	</form>

	<div class="rk-kpis" style="--rk-tiles: 5">
		<div class="rk-kpi rk-kpi--lead">
			<div class="rk-kpi__label">Purchases</div>
			<div class="rk-kpi__value">{{ $money( $s['total'] ) }}</div>
		</div>
		<div class="rk-kpi">
			<div class="rk-kpi__label">Invoices</div>
			<div class="rk-kpi__value">{{ number_format( $s['invoices'] ) }}</div>
		</div>
		<div class="rk-kpi">
			<div class="rk-kpi__label">Items received</div>
			<div class="rk-kpi__value">{{ number_format( $s['items'] ) }}</div>
		</div>
		<div class="rk-kpi">
			<div class="rk-kpi__label">Paid</div>
			<div class="rk-kpi__value">{{ $money( $s['paid'] ) }}</div>
		</div>
		<div class="rk-kpi {{ $s['unpaid'] > 0 ? 'rk-kpi--warn' : '' }}">
			<div class="rk-kpi__label">Still owed</div>
			<div class="rk-kpi__value">{{ $money( $s['unpaid'] ) }}</div>
		</div>
	</div>

	<div class="rk-card">
		<div class="rk-card__head">
			<h3>{{ number_format( $s['invoices'] ) }} {{ $s['invoices'] === 1 ? 'invoice' : 'invoices' }}</h3>
			<span class="rk-muted">Newest first</span>
		</div>
		<div class="table-responsive">
			<table id="stockTable" class="table table-hover rk-table">
				<thead>
				<tr>
					<th>#</th>
					<th>Date</th>
					<th>Vendor</th>
					<th>Vendor invoice #</th>
					<th class="num">Items</th>
					<th class="num">Sub total</th>
					<th class="num">Discount</th>
					<th class="num">Delivery</th>
					<th class="num">Total</th>
					<th class="num">Paid</th>
					<th class="num">Owed</th>
				</tr>
				</thead>
				<tbody>
				@foreach($invoices as $r)
					<tr>
						<td>
							@if($canView)
								<a href="{{ route('inventory.invoice', $r->id) }}" target="_blank">#{{ $r->id }}</a>
							@else
								#{{ $r->id }}
							@endif
						</td>
						<td class="text-nowrap">{{ \Carbon\Carbon::parse( $r->date )->format( 'd-M-Y' ) }}</td>
						<td>{{ $r->vendor ?? 'Deleted vendor' }}</td>
						<td>{{ $r->invoice_no ?: '—' }}</td>
						<td class="num">{{ number_format( $r->items ) }}</td>
						<td class="num">{{ number_format( (float) $r->sub_total ) }}</td>
						<td class="num">{{ number_format( (float) $r->discount ) }}</td>
						<td class="num">{{ number_format( (float) $r->delivery_charges ) }}</td>
						<td class="num"><b>{{ number_format( (float) $r->total ) }}</b></td>
						<td class="num">{{ number_format( (float) $r->credit ) }}</td>
						<td class="num {{ $r->debit > 0 ? 'rk-bad' : '' }}">{{ number_format( (float) $r->debit ) }}</td>
					</tr>
				@endforeach
				</tbody>
				<tfoot>
				<tr>
					<th colspan="4">Total</th>
					<th class="num">{{ number_format( $s['items'] ) }}</th>
					<th class="num">{{ number_format( $invoices->sum('sub_total') ) }}</th>
					<th class="num">{{ number_format( $invoices->sum('discount') ) }}</th>
					<th class="num">{{ number_format( $invoices->sum('delivery_charges') ) }}</th>
					<th class="num">{{ number_format( $s['total'] ) }}</th>
					<th class="num">{{ number_format( $s['paid'] ) }}</th>
					<th class="num {{ $s['unpaid'] > 0 ? 'rk-bad' : '' }}">{{ number_format( $s['unpaid'] ) }}</th>
				</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>
@endsection

@include('admin.partials.report-kit')
@include('plugins.DataTables')

@push('footer')
	<script>
		$(function () {
			$('#stockTable').DataTable({
				ordering: false,
				autoWidth: false,
				searching: false,
				pageLength: 100,
				lengthMenu: [[50, 100, 500], [50, 100, 500]],
				language: {emptyTable: 'No stock invoices in this period.'}
			})
			$('#f-vendor').on('change', function () { $('#stock-filters').trigger('submit') })
		})
	</script>
@endpush
