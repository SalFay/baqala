@extends('admin.layouts.admin')
@section('page-title','Dashboard')
@section('heading','Dashboard')

@section('breadcrumbs')
	<span class="breadcrumb-item active">Dashboard</span>
@endsection

@php
	$cur = $current;
	$cmp = $compare;
	$money = fn( $v ) => 'Rs ' . number_format( (float) $v );
	$pct = fn( $v ) => $v === null ? '—' : number_format( $v * 100, 1 ) . '%';
	$fmt = fn( $d ) => $d->format( 'd-M-Y' );
	$period = fn( $s ) => $s['from']->equalTo( $s['to'] ) ? $fmt( $s['from'] ) : $fmt( $s['from'] ) . ' – ' . $fmt( $s['to'] );
	$change = fn( $key ) => $cmp ? app( \App\Services\DashboardService::class )->change( $cur[ $key ], $cmp[ $key ] ) : null;

	// [label, key, formatter, higher is better]
	$kpis = [
		[ 'Net sales', 'net_sales', $money, true ],
		[ 'Orders', 'orders', fn( $v ) => number_format( $v ), true ],
		[ 'Average order', 'avg_order', $money, true ],
		[ 'Items sold', 'items', fn( $v ) => number_format( $v ), true ],
		[ 'Discounts given', 'discount', $money, false ],
		[ 'Delivery charges', 'delivery', $money, true ],
	];
	$compareLabels = [ 'previous' => 'Previous period', 'last_year' => 'Same period last year', 'none' => 'No comparison' ];
@endphp

@section('content')
<div class="dash">

	{{-- Range and comparison --}}
	<form method="get" class="dash-toolbar">
		<div class="dash-toolbar__title">
			<div class="dash-eyebrow">{{ option('title') ?: config('app.name') }}</div>
			<h2>{{ $period( $cur ) }}</h2>
			@if($cmp)
				<div class="dash-muted">compared with {{ $period( $cmp ) }}</div>
			@endif
		</div>
		<div class="dash-toolbar__controls">
			<div class="dash-presets">
				@foreach($presets as $label => $value)
					<a href="?range={{ urlencode($value) }}&compare={{ $compareMode }}"
					   class="dash-chip {{ $value === $range ? 'is-active' : '' }}">{{ $label }}</a>
				@endforeach
			</div>
			<div class="dash-pick">
				<input type="text" name="range" id="dash-range" value="{{ $range }}" class="form-control" autocomplete="off">
				<select name="compare" class="form-control">
					@foreach($compareLabels as $value => $label)
						<option value="{{ $value }}" {{ $compareMode === $value ? 'selected' : '' }}>{{ $label }}</option>
					@endforeach
				</select>
				<button type="submit" class="btn btn-primary">Apply</button>
			</div>
		</div>
	</form>

	{{-- Headline figures --}}
	<div class="dash-kpis">
		@foreach($kpis as [ $label, $key, $f, $upIsGood ])
			@php $c = $change( $key ); @endphp
			<div class="dash-kpi {{ $key === 'net_sales' ? 'dash-kpi--lead' : '' }}">
				<div class="dash-kpi__label">{{ $label }}</div>
				<div class="dash-kpi__value">{{ $f( $cur[ $key ] ) }}</div>
				@if($cmp)
					<div class="dash-kpi__cmp">
						@if($c !== null)
							<span class="dash-delta {{ ( $c >= 0 ) === $upIsGood ? 'is-good' : 'is-bad' }}">
								{{ $c >= 0 ? '▲' : '▼' }} {{ $pct( abs( $c ) ) }}
							</span>
						@endif
						<span class="dash-muted">vs {{ $f( $cmp[ $key ] ) }}</span>
					</div>
				@endif
			</div>
		@endforeach
	</div>

	{{-- Profit --}}
	<div class="dash-card">
		<div class="dash-card__head"><h3>Profit</h3></div>
		@if($cur['gross_profit'] === null)
			<div class="dash-notice">
				<strong>Profit can't be calculated yet.</strong>
				Only {{ $pct( $cur['cost_coverage'] ) }} of these sales have a purchase cost recorded: products
				have no purchase price, and the POS has been saving the sale price as the cost.
				Set purchase prices in <a href="{{ url('admin/products') }}">Products</a> and profit will show here.
				@if($cur['margin'] !== null)
					<div class="dash-muted">Margin on the sales that do have a cost: {{ $pct( $cur['margin'] ) }}</div>
				@endif
			</div>
			<div class="dash-row4">
				<div><span class="dash-muted">Item sales</span><b>{{ $money( $cur['item_sales'] ) }}</b></div>
				<div><span class="dash-muted">Discounts</span><b>− {{ $money( $cur['discount'] ) }}</b></div>
				<div><span class="dash-muted">Expenses</span><b>− {{ $money( $cur['expenses'] ) }}</b></div>
				<div><span class="dash-muted">Unpaid orders</span><b>{{ $money( $cur['unpaid'] ) }}</b></div>
			</div>
		@else
			<div class="dash-row4">
				<div><span class="dash-muted">Gross profit</span><b>{{ $money( $cur['gross_profit'] ) }}</b>
					<span class="dash-muted">{{ $pct( $cur['margin'] ) }} margin</span></div>
				<div><span class="dash-muted">Expenses</span><b>− {{ $money( $cur['expenses'] ) }}</b></div>
				<div><span class="dash-muted">Net profit</span><b>{{ $money( $cur['net_profit'] ) }}</b>
					@if($cmp && $cmp['net_profit'] !== null)
						<span class="dash-muted">vs {{ $money( $cmp['net_profit'] ) }}</span>
					@endif
				</div>
				<div><span class="dash-muted">Unpaid orders</span><b>{{ $money( $cur['unpaid'] ) }}</b></div>
			</div>
		@endif
	</div>

	{{-- Trend + categories --}}
	<div class="row">
		<div class="col-xl-8">
			<div class="dash-card">
				<div class="dash-card__head"><h3>Daily sales</h3>
					<span class="dash-muted">{{ number_format( $cur['orders'] ) }} orders</span></div>
				<div id="chart-daily"></div>
			</div>
		</div>
		<div class="col-xl-4">
			<div class="dash-card">
				<div class="dash-card__head"><h3>Sales by category</h3></div>
				<div id="chart-categories"></div>
				<table class="dash-table">
					@foreach($categories as $c)
						<tr>
							<td>{{ $c['name'] }}</td>
							<td class="num">{{ $money( $c['sales'] ) }}</td>
							<td class="num dash-muted">{{ $pct( $c['share'] ) }}</td>
							<td class="num">
								@if($c['change'] !== null)
									<span class="dash-delta {{ $c['change'] >= 0 ? 'is-good' : 'is-bad' }}">{{ $c['change'] >= 0 ? '▲' : '▼' }} {{ $pct( abs( $c['change'] ) ) }}</span>
								@endif
							</td>
						</tr>
					@endforeach
				</table>
			</div>
		</div>
	</div>

	{{-- Hours + products --}}
	<div class="row">
		<div class="col-xl-5">
			<div class="dash-card">
				<div class="dash-card__head"><h3>Busiest hours</h3><span class="dash-muted">orders by hour rung up</span></div>
				<div id="chart-hours"></div>
			</div>
		</div>
		<div class="col-xl-7">
			<div class="dash-card">
				<div class="dash-card__head"><h3>Top products</h3></div>
				<table class="dash-table dash-table--lined">
					<thead><tr><th>Product</th><th class="num">Qty</th><th class="num">Sales</th><th class="num">Share</th>
						@if($cmp)<th class="num">Change</th>@endif</tr></thead>
					<tbody>
					@forelse($products as $p)
						<tr>
							<td>{{ $p['name'] }}</td>
							<td class="num">{{ number_format( $p['qty'] ) }}</td>
							<td class="num">{{ $money( $p['sales'] ) }}</td>
							<td class="num dash-muted">{{ $pct( $p['share'] ) }}</td>
							@if($cmp)
								<td class="num">
									@if($p['change'] !== null)
										<span class="dash-delta {{ $p['change'] >= 0 ? 'is-good' : 'is-bad' }}">{{ $p['change'] >= 0 ? '▲' : '▼' }} {{ $pct( abs( $p['change'] ) ) }}</span>
									@else
										<span class="dash-muted">new</span>
									@endif
								</td>
							@endif
						</tr>
					@empty
						<tr><td colspan="5" class="dash-muted">No sales in this period.</td></tr>
					@endforelse
					</tbody>
				</table>
			</div>
		</div>
	</div>

	{{-- Months + cashiers --}}
	<div class="row">
		<div class="col-xl-8">
			<div class="dash-card">
				<div class="dash-card__head"><h3>Last 12 months</h3></div>
				<div id="chart-months"></div>
				<div class="table-responsive">
					<table class="dash-table dash-table--lined">
						<thead><tr><th>Month</th><th class="num">Orders</th><th class="num">Net sales</th>
							<th class="num">Avg order</th><th class="num">Discounts</th><th class="num">Delivery</th></tr></thead>
						<tbody>
						@foreach(array_reverse($months) as $m)
							<tr>
								<td>{{ $m['label'] }}</td>
								<td class="num">{{ number_format( $m['orders'] ) }}</td>
								<td class="num">{{ $money( $m['sales'] ) }}</td>
								<td class="num">{{ $money( $m['avg'] ) }}</td>
								<td class="num">{{ $money( $m['discount'] ) }}</td>
								<td class="num">{{ $money( $m['delivery'] ) }}</td>
							</tr>
						@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-xl-4">
			<div class="dash-card">
				<div class="dash-card__head"><h3>Cashiers</h3></div>
				<table class="dash-table dash-table--lined">
					<thead><tr><th>Name</th><th class="num">Orders</th><th class="num">Sales</th><th class="num">Avg</th></tr></thead>
					<tbody>
					@forelse($cashiers as $c)
						<tr>
							<td>{{ $c['name'] }}</td>
							<td class="num">{{ number_format( $c['orders'] ) }}</td>
							<td class="num">{{ $money( $c['sales'] ) }}</td>
							<td class="num dash-muted">{{ $money( $c['avg'] ) }}</td>
						</tr>
					@empty
						<tr><td colspan="4" class="dash-muted">No sales in this period.</td></tr>
					@endforelse
					</tbody>
				</table>
			</div>

			<div class="dash-card">
				<div class="dash-card__head"><h3>Money position</h3><span class="dash-muted">today</span></div>
				<table class="dash-table">
					@foreach($balances['banks'] as $b)
						<tr><td>{{ $b['name'] }}</td><td class="num">{{ $money( $b['amount'] ) }}</td></tr>
					@endforeach
					<tr><td>Customers owe</td><td class="num">{{ $money( $balances['receivable'] ) }}</td></tr>
					<tr><td>Owed to vendors</td><td class="num">{{ $money( $balances['payable'] ) }}</td></tr>
					<tr><td>Stock value</td><td class="num">{{ $money( $balances['stock'] ) }}</td></tr>
				</table>
			</div>

			<div class="dash-card">
				<div class="dash-card__head"><h3>Expenses</h3><span class="dash-muted">this period</span></div>
				<table class="dash-table">
					@forelse($expenses as $e)
						<tr><td>{{ $e['name'] }}</td><td class="num">{{ $money( $e['amount'] ) }}</td></tr>
					@empty
						<tr><td class="dash-muted">No expense types set up.</td></tr>
					@endforelse
				</table>
			</div>
		</div>
	</div>
</div>
@endsection

@push('head')
	<link rel="stylesheet" href="{{asset('plugins/js/pickers/flatpickr/flatpickr.min.css')}}">
	<style>
		.dash { --ink: #1f2933; --muted: #6b7785; --line: #e6e9ee; --card: #fff; --good: #1a8f5a; --bad: #c2412d; --accent: #12796b;
			color: var(--ink); }
		.dash h2 { font-size: 1.35rem; font-weight: 600; margin: 0; }
		.dash h3 { font-size: .95rem; font-weight: 600; margin: 0; }
		.dash-muted { color: var(--muted); font-size: .8rem; }
		.dash-eyebrow { color: var(--accent); font-size: .72rem; letter-spacing: .08em; text-transform: uppercase; font-weight: 600; }
		.dash-toolbar { display: flex; flex-wrap: wrap; gap: 1rem; justify-content: space-between; align-items: flex-end; margin-bottom: 1rem; }
		.dash-toolbar__controls { display: flex; flex-direction: column; gap: .5rem; align-items: flex-end; }
		.dash-presets { display: flex; flex-wrap: wrap; gap: .35rem; justify-content: flex-end; }
		.dash-chip { padding: .2rem .65rem; border: 1px solid var(--line); border-radius: 999px; font-size: .78rem; color: var(--ink); background: var(--card); }
		.dash-chip:hover { text-decoration: none; border-color: var(--accent); color: var(--accent); }
		.dash-chip.is-active { background: var(--accent); border-color: var(--accent); color: #fff; }
		.dash-pick { display: flex; gap: .4rem; flex-wrap: wrap; justify-content: flex-end; }
		.dash-pick .form-control { width: auto; min-width: 170px; }
		.dash-kpis { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: .75rem; margin-bottom: 1rem; }
		.dash-kpi { background: var(--card); border: 1px solid var(--line); border-radius: 10px; padding: .85rem 1rem; }
		.dash-kpi--lead { background: linear-gradient(135deg, #12796b, #0f5f55); color: #fff; border: 0; }
		.dash-kpi--lead .dash-muted, .dash-kpi--lead .dash-kpi__label { color: rgba(255,255,255,.8); }
		.dash-kpi__label { font-size: .75rem; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; }
		.dash-kpi__value { font-size: 1.45rem; font-weight: 700; margin: .2rem 0; font-variant-numeric: tabular-nums; white-space: nowrap; }
		.dash-kpi__cmp { display: flex; gap: .4rem; align-items: center; flex-wrap: wrap; }
		.dash-delta { font-size: .75rem; font-weight: 600; padding: .05rem .4rem; border-radius: 4px; white-space: nowrap; }
		.dash-delta.is-good { color: var(--good); background: rgba(26,143,90,.1); }
		.dash-delta.is-bad { color: var(--bad); background: rgba(194,65,45,.1); }
		.dash-kpi--lead .dash-delta { background: rgba(255,255,255,.18); color: #fff; }
		.dash-card { background: var(--card); border: 1px solid var(--line); border-radius: 10px; padding: 1rem 1.1rem; margin-bottom: 1rem; }
		.dash-card__head { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: .6rem; gap: .5rem; }
		.dash-notice { background: #fff8e6; border: 1px solid #f3dfa8; border-radius: 8px; padding: .7rem .9rem; margin-bottom: .8rem; font-size: .88rem; }
		.dash-row4 { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .75rem; }
		.dash-row4 > div { display: flex; flex-direction: column; }
		.dash-row4 b { font-size: 1.1rem; font-variant-numeric: tabular-nums; }
		.dash-table { width: 100%; font-size: .85rem; }
		.dash-table td, .dash-table th { padding: .35rem .25rem; text-align: left; }
		.dash-table th { color: var(--muted); font-weight: 600; font-size: .75rem; text-transform: uppercase; }
		.dash-table--lined tr + tr td, .dash-table--lined tbody tr td { border-top: 1px solid var(--line); }
		.dash-table .num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
		@media (max-width: 1399px) { .dash-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
		@media (max-width: 767px) {
			.dash-kpis, .dash-row4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
			.dash-toolbar__controls { align-items: stretch; width: 100%; }
			.dash-presets, .dash-pick { justify-content: flex-start; }
			.dash-pick .form-control { flex: 1 1 140px; min-width: 0; }
		}
	</style>
@endpush

@push('footer')
	<script src="{{asset('plugins/js/pickers/flatpickr/flatpickr.min.js')}}"></script>
	<script>
		$(function () {
			flatpickr('#dash-range', {mode: 'range', dateFormat: 'Y-m-d', altInput: true, altFormat: 'd-M-Y'})

			const rs = v => 'Rs ' + Math.round(v).toLocaleString('en-PK')
			const accent = '#12796b', soft = '#b8c4cf'
			const base = {
				chart: {toolbar: {show: false}, fontFamily: 'inherit', animations: {enabled: false}},
				dataLabels: {enabled: false},
				grid: {borderColor: '#eef1f4', strokeDashArray: 3},
				yaxis: {labels: {formatter: v => v >= 1000 ? Math.round(v / 1000) + 'k' : Math.round(v)}},
			}

			const daily = @json($daily);
			new ApexCharts(document.querySelector('#chart-daily'), Object.assign({}, base, {
				chart: Object.assign({}, base.chart, {type: 'area', height: 300}),
				series: [{name: 'This period', data: daily.map(d => d.sales)}]
					.concat(@json((bool) $compare) ? [{name: 'Compared period', data: daily.map(d => d.compare)}] : []),
				xaxis: {categories: daily.map(d => d.label), tickAmount: Math.min(daily.length, 12), labels: {rotate: 0}},
				colors: [accent, soft],
				stroke: {width: [2.5, 2], curve: 'smooth', dashArray: [0, 5]},
				fill: {type: 'gradient', gradient: {opacityFrom: .35, opacityTo: 0}},
				tooltip: {y: {formatter: rs}, x: {formatter: (v, o) => daily[o.dataPointIndex] ? daily[o.dataPointIndex].label + ' · ' + daily[o.dataPointIndex].orders + ' orders' : v}},
				legend: {position: 'top', horizontalAlign: 'right'},
			})).render()

			const cats = @json($categories);
			new ApexCharts(document.querySelector('#chart-categories'), {
				chart: {type: 'donut', height: 230, fontFamily: 'inherit'},
				series: cats.map(c => c.sales), labels: cats.map(c => c.name),
				legend: {show: false}, dataLabels: {enabled: false},
				plotOptions: {pie: {donut: {size: '62%', labels: {show: true, total: {show: true, label: 'Total', formatter: w => rs(w.globals.seriesTotals.reduce((a, b) => a + b, 0))}}}}},
				tooltip: {y: {formatter: rs}},
				colors: ['#12796b', '#2f9e8f', '#5cb8a8', '#8fd1c4', '#e0a75e', '#d9774b', '#a65d8f', '#6a7fdb', '#9aa5b1'],
			}).render()

			const hours = @json($hourly);
			new ApexCharts(document.querySelector('#chart-hours'), Object.assign({}, base, {
				chart: Object.assign({}, base.chart, {type: 'bar', height: 280}),
				series: [{name: 'Orders', data: hours.map(h => h.orders)}],
				xaxis: {categories: hours.map(h => h.label)},
				yaxis: {labels: {formatter: v => Math.round(v)}},
				colors: [accent], plotOptions: {bar: {borderRadius: 3, columnWidth: '60%'}},
				tooltip: {y: {formatter: (v, o) => v + ' orders · ' + rs(hours[o.dataPointIndex].sales)}},
			})).render()

			const months = @json($months);
			new ApexCharts(document.querySelector('#chart-months'), Object.assign({}, base, {
				chart: Object.assign({}, base.chart, {type: 'bar', height: 260}),
				series: [{name: 'Net sales', data: months.map(m => m.sales)}],
				xaxis: {categories: months.map(m => m.label)},
				colors: [accent], plotOptions: {bar: {borderRadius: 3, columnWidth: '55%'}},
				tooltip: {y: {formatter: (v, o) => rs(v) + ' · ' + months[o.dataPointIndex].orders + ' orders'}},
			})).render()
		})
	</script>
@endpush
