@extends('admin.layouts.admin')

@section('page-title', 'Product Sold')
@section('heading', 'Product Sold')
@section('breadcrumbs', 'Product Sold')

@php
	$range = $f['range'];
	$money = fn( $v ) => 'Rs ' . number_format( (float) $v );
	$s = $summary;
	$params = [ 'category' => $f['category'], 'q' => $f['search'], 'sort' => $sort === 'qty' ? 'qty' : '' ];
	$link = fn( array $over ) => '?' . http_build_query( array_filter( array_merge( $params, [ 'range' => $range->value() ], $over ),
		fn( $v ) => $v !== '' && $v !== null && $v !== 0 ) );
	$top = $products[0] ?? null;
@endphp

@section('content')
<div class="rk">

	@include('admin.partials.report-head', [
		'eyebrow' => 'Product Sold',
		'range'   => $range,
		'presets' => $presets,
		'params'  => $params,
		'note'    => implode( ' · ', array_filter( [
			'By sale date',
			$f['category'] ? ( $categories[ $f['category'] ] ?? null ) : null,
			$f['search'] !== '' ? '“' . $f['search'] . '”' : null,
		] ) ),
	])

	<form method="get" class="rk-filters" id="product-filters">
		<input type="hidden" name="sort" value="{{ $sort === 'qty' ? 'qty' : '' }}">
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
			<label for="f-q">Product</label>
			<input type="search" name="q" id="f-q" value="{{ $f['search'] }}" class="form-control" placeholder="e.g. Pizza">
		</div>
		<div class="rk-actions">
			<button type="submit" class="btn btn-primary">Apply</button>
			<a href="{{ url()->current() }}" class="btn btn-light">Reset</a>
		</div>
	</form>

	<div class="rk-kpis" style="--rk-tiles: 6">
		<div class="rk-kpi rk-kpi--lead">
			<div class="rk-kpi__label">Item sales</div>
			<div class="rk-kpi__value">{{ $money( $s['sales'] ) }}</div>
		</div>
		<div class="rk-kpi">
			<div class="rk-kpi__label">Items sold</div>
			<div class="rk-kpi__value">{{ number_format( $s['items'] ) }}</div>
		</div>
		<div class="rk-kpi">
			<div class="rk-kpi__label">Products</div>
			<div class="rk-kpi__value">{{ number_format( $s['products'] ) }}</div>
		</div>
		<div class="rk-kpi">
			<div class="rk-kpi__label">Orders</div>
			<div class="rk-kpi__value">{{ number_format( $s['orders'] ) }}</div>
		</div>
		<div class="rk-kpi">
			<div class="rk-kpi__label">After discounts</div>
			<div class="rk-kpi__value">{{ $money( $s['net'] ) }}</div>
		</div>
		<div class="rk-kpi">
			<div class="rk-kpi__label">Best seller</div>
			<div class="rk-kpi__value" style="font-size: 1rem" title="{{ $top['name'] ?? '' }}">{{ $top['name'] ?? '—' }}</div>
			@if($top)
				<div class="rk-muted">{{ $money( $top['sales'] ) }} · {{ number_format( $top['qty'] ) }} sold</div>
			@endif
		</div>
	</div>

	<div class="rk-grid">
		<div class="rk-card">
			<div class="rk-card__head">
				<h3>{{ number_format( count( $products ) ) }} {{ count( $products ) === 1 ? 'product' : 'products' }}</h3>
				<span class="rk-sort">
					<span class="rk-muted">Sort by</span>
					<a href="{{ $link( [ 'sort' => '' ] ) }}" class="rk-chip {{ $sort === 'sales' ? 'is-active' : '' }}">Sales</a>
					<a href="{{ $link( [ 'sort' => 'qty' ] ) }}" class="rk-chip {{ $sort === 'qty' ? 'is-active' : '' }}">Quantity</a>
				</span>
			</div>
			<div class="table-responsive">
				<table id="productTable" class="table table-hover rk-table">
					<thead>
					<tr>
						<th>#</th>
						<th>Product</th>
						<th class="num">Price</th>
						<th class="num">Qty sold</th>
						<th class="num">Orders</th>
						<th class="num">Sales</th>
						<th class="num">Discount</th>
						<th class="num">Net</th>
						<th>Share of sales</th>
					</tr>
					</thead>
					<tbody>
					@foreach($products as $i => $p)
						<tr>
							<td class="rk-sub">{{ $i + 1 }}</td>
							<td>{{ $p['name'] }}<div class="rk-sub">{{ $p['category'] }}</div></td>
							<td class="num">{{ $p['price'] }}</td>
							<td class="num">{{ number_format( $p['qty'] ) }}</td>
							<td class="num">{{ number_format( $p['orders'] ) }}</td>
							<td class="num"><b>{{ number_format( $p['sales'] ) }}</b></td>
							<td class="num">{{ number_format( $p['discount'] ) }}</td>
							<td class="num">{{ number_format( $p['net'] ) }}</td>
							<td style="min-width: 130px">
								<div class="d-flex align-items-center" style="gap: .4rem">
									<div class="rk-bar flex-grow-1"><span style="width: {{ round( $p['share'] * 100, 1 ) }}%"></span></div>
									<span class="rk-sub" style="min-width: 38px; text-align: right">{{ number_format( $p['share'] * 100, 1 ) }}%</span>
								</div>
							</td>
						</tr>
					@endforeach
					</tbody>
					<tfoot>
					<tr>
						<th colspan="3">Total</th>
						<th class="num">{{ number_format( $s['items'] ) }}</th>
						<th class="num">{{ number_format( $s['orders'] ) }}</th>
						<th class="num">{{ number_format( $s['sales'] ) }}</th>
						<th class="num">{{ number_format( $s['discount'] ) }}</th>
						<th class="num">{{ number_format( $s['net'] ) }}</th>
						<th></th>
					</tr>
					</tfoot>
				</table>
			</div>
			<div class="rk-muted mt-2">
				Orders counts each order once per product. Discount is each order's discount shared across its items by value.
				Price shows a range when the price changed during the period.
			</div>
		</div>

		<div class="rk-card rk-side">
			<div class="rk-card__head"><h3>By category</h3></div>
			<div class="rk-cats">
			@forelse($byCategory as $c)
				<div>
					<div class="d-flex justify-content-between" style="font-size: .85rem">
						<span>{{ $c['name'] }}</span>
						<span><b>{{ number_format( $c['sales'] ) }}</b> <span class="rk-sub">· {{ number_format( $c['qty'] ) }} sold</span></span>
					</div>
					<div class="rk-bar" style="margin-top: .2rem"><span style="width: {{ round( $c['share'] * 100, 1 ) }}%"></span></div>
				</div>
			@empty
				<div class="rk-muted">Nothing sold in this period.</div>
			@endforelse
			</div>
		</div>
	</div>
</div>
@endsection

@include('admin.partials.report-kit')
@include('plugins.DataTables')

@push('footer')
	<script>
		$(function () {
			$('#productTable').DataTable({
				ordering: false,
				autoWidth: false,
				searching: false,
				paging: {{ count( $products ) > 100 ? 'true' : 'false' }},
				pageLength: 100,
				info: false,
				language: {emptyTable: 'Nothing sold for these filters.'}
			})
			$('#f-category').on('change', function () { $('#product-filters').trigger('submit') })
		})
	</script>
@endpush
