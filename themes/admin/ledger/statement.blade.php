@extends('admin.layouts.admin')

@php
	$labels = [ 'customer' => 'Customer statement', 'vendor' => 'Vendor statement', 'bank' => 'Bank statement' ];
	$lists = [ 'customer' => [ 'Customers', url('admin/customers') ], 'vendor' => [ 'Vendors', url('admin/vendors') ], 'bank' => [ 'Banks', url('admin/bank') ] ];
	$cfg = \App\Services\LedgerService::PARTIES[ $kind ];
	$range = $f['range'];
	$s = $summary;
	$money = fn( $v ) => ( $v < 0 ? '− ' : '' ) . 'Rs ' . number_format( abs( (float) $v ) );
	$isCustomer = $kind === 'customer';
@endphp

@section('page-title', $labels[ $kind ] . ' – ' . $name)
@section('heading', $name)
@section('breadcrumbs')<a href="{{ $lists[ $kind ][1] }}">{{ $lists[ $kind ][0] }}</a> &nbsp;/&nbsp; Statement @endsection

@section('content')
<div class="rk">

	@include('admin.partials.report-head', [
		'eyebrow' => $labels[ $kind ],
		'range'   => $range,
		'presets' => \App\Support\DateRange::presets(),
		'params'  => [ 'q' => $f['search'] ],
		'note'    => $name . ' · by entry date' . ( $f['search'] !== '' ? ' · “' . $f['search'] . '”' : '' ),
	])

	<form method="get" class="rk-filters">
		<div class="rk-field rk-field--range">
			<label for="f-range">Date</label>
			<input type="text" name="range" id="f-range" value="{{ $range->value() }}" class="form-control rk-range" autocomplete="off">
		</div>
		<div class="rk-field rk-field--grow">
			<label for="f-q">Description</label>
			<input type="search" name="q" id="f-q" value="{{ $f['search'] }}" class="form-control" placeholder="{{ $isCustomer ? 'e.g. 21825' : 'Search the description' }}">
		</div>
		<div class="rk-actions">
			<button type="submit" class="btn btn-primary">Apply</button>
			<a href="{{ url()->current() }}" class="btn btn-light">Reset</a>
		</div>
	</form>

	<div class="rk-kpis" style="--rk-tiles: {{ $isCustomer ? 6 : 5 }}">
		<div class="rk-kpi">
			<div class="rk-kpi__label">Opening balance</div>
			<div class="rk-kpi__value">{{ $money( $s['opening'] ) }}</div>
			<div class="rk-muted">before {{ $range->from->format('d-M-Y') }}</div>
		</div>
		<div class="rk-kpi">
			<div class="rk-kpi__label">Debit</div>
			<div class="rk-kpi__value">{{ $money( $s['debit'] ) }}</div>
			<div class="rk-muted">{{ $isCustomer ? 'sales charged' : 'money out' }}</div>
		</div>
		<div class="rk-kpi">
			<div class="rk-kpi__label">Credit</div>
			<div class="rk-kpi__value">{{ $money( $s['credit'] ) }}</div>
			<div class="rk-muted">{{ $isCustomer ? 'payments received' : 'money in' }}</div>
		</div>
		@if($isCustomer)
			<div class="rk-kpi">
				<div class="rk-kpi__label">Discount</div>
				<div class="rk-kpi__value">{{ $money( $s['discount'] ) }}</div>
			</div>
		@endif
		<div class="rk-kpi rk-kpi--lead">
			<div class="rk-kpi__label">Closing balance</div>
			<div class="rk-kpi__value">{{ $money( $s['closing'] ) }}</div>
			<div class="rk-muted">{{ strtolower( $cfg['balance'] ) }} on {{ $range->to->format('d-M-Y') }}</div>
		</div>
		<div class="rk-kpi">
			<div class="rk-kpi__label">Balance today</div>
			<div class="rk-kpi__value">{{ $money( $s['all_time'] ) }}</div>
			<div class="rk-muted">all entries</div>
		</div>
	</div>

	<div class="rk-card">
		<div class="rk-card__head">
			<h3>{{ number_format( $s['entries'] ) }} {{ $s['entries'] === 1 ? 'entry' : 'entries' }}</h3>
			<span class="rk-muted">
				Oldest first · balance = {{ $isCustomer ? 'debit − credit − discount' : 'credit − debit' }}, carried from the opening balance
				@if($f['search'] !== '') · running balance hidden while searching @endif
			</span>
		</div>
		<div class="table-responsive">
			<table id="ledgerTable" class="table table-hover rk-table">
				<thead>
				<tr>
					<th>Date</th>
					<th>Description</th>
					<th class="num">Debit</th>
					<th class="num">Credit</th>
					@if($isCustomer)<th class="num">Discount</th>@endif
					<th class="num">Balance</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<th colspan="2">Totals for the period</th>
					<th class="num">{{ number_format( $s['debit'], 2 ) }}</th>
					<th class="num">{{ number_format( $s['credit'], 2 ) }}</th>
					@if($isCustomer)<th class="num">{{ number_format( $s['discount'], 2 ) }}</th>@endif
					<th class="num">{{ number_format( $s['closing'], 2 ) }}</th>
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
			const num = (d, t) => t === 'display'
				? (Number(d) ? Number(d).toLocaleString('en-PK', {maximumFractionDigits: 2}) : '<span class="rk-sub">0</span>')
				: d
			const bal = (d, t) => t !== 'display' ? d : (d === null ? '<span class="rk-sub">—</span>'
				: '<b' + (d < 0 ? ' class="rk-bad"' : '') + '>' + Number(d).toLocaleString('en-PK', {maximumFractionDigits: 2}) + '</b>')

			$('#ledgerTable').DataTable({
				processing: true,
				serverSide: true,
				ordering: false,
				searching: false,
				autoWidth: false,
				pageLength: 100,
				lengthMenu: [[50, 100, 500, 1000], [50, 100, 500, 1000]],
				language: {emptyTable: 'No entries in this period.', processing: 'Loading…'},
				ajax: {
					url: "{{ route( $cfg['route'] . '.ajax', $party->id ) }}",
					type: 'post',
					data: {range: @json($range->value()), q: @json($f['search'])}
				},
				columns: [
					{data: 'date', className: 'text-nowrap'},
					{data: 'comments'},
					{data: 'debit', className: 'num', render: num},
					{data: 'credit', className: 'num', render: num},
					@if($isCustomer) {data: 'discount', className: 'num', render: num}, @endif
					{data: 'balance', className: 'num', render: bal}
				]
			})
		})
	</script>
@endpush
