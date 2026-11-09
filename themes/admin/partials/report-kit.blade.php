{{--
	Shared look for list and report pages: toolbar with date presets and filters,
	total tiles, and a tidy table. Same colours and spacing as the dashboard.
	Include once per page: @include('admin.partials.report-kit')
--}}
@once
@push('head')
	<link rel="stylesheet" href="{{asset('plugins/js/pickers/flatpickr/flatpickr.min.css')}}">
	<style>
		.rk { --ink: #1f2933; --muted: #6b7785; --line: #e6e9ee; --card: #fff; --good: #1a8f5a; --bad: #c2412d; --accent: #12796b; color: var(--ink); }
		.rk h2 { font-size: 1.35rem; font-weight: 600; margin: 0; }
		.rk-eyebrow { color: var(--accent); font-size: .72rem; letter-spacing: .08em; text-transform: uppercase; font-weight: 600; }
		.rk-muted, .rk-sub { color: var(--muted); font-size: .78rem; }
		.rk-sub { font-size: .72rem; line-height: 1.2; }
		.rk-head { display: flex; flex-wrap: wrap; gap: .75rem 1rem; justify-content: space-between; align-items: flex-end; margin-bottom: .75rem; }
		.rk-presets { display: flex; flex-wrap: wrap; gap: .35rem; }
		.rk-chip { padding: .2rem .65rem; border: 1px solid var(--line); border-radius: 999px; font-size: .78rem; color: var(--ink); background: var(--card); }
		.rk-chip:hover { text-decoration: none; border-color: var(--accent); color: var(--accent); }
		.rk-chip.is-active { background: var(--accent); border-color: var(--accent); color: #fff; }
		.rk-filters { display: flex; flex-wrap: wrap; gap: .6rem; align-items: flex-end; background: var(--card); border: 1px solid var(--line); border-radius: 10px; padding: .75rem .9rem; margin-bottom: 1rem; }
		.rk-field { display: flex; flex-direction: column; gap: .2rem; min-width: 150px; flex: 0 1 auto; }
		.rk-field label { font-size: .72rem; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; margin: 0; }
		.rk-field--range { min-width: 230px; }
		.rk-field--grow { flex: 1 1 200px; }
		.rk-actions { display: flex; gap: .4rem; margin-left: auto; }
		.rk-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: .75rem; margin-bottom: 1rem; }
		@media (min-width: 1200px) { .rk-kpis { grid-template-columns: repeat(var(--rk-tiles, 6), minmax(0, 1fr)); } }
		.rk-kpi { background: var(--card); border: 1px solid var(--line); border-radius: 10px; padding: .75rem .9rem; min-width: 0; }
		.rk-kpi--lead { background: linear-gradient(135deg, #12796b, #0f5f55); color: #fff; border: 0; }
		.rk-kpi--lead .rk-kpi__label, .rk-kpi--lead .rk-muted { color: rgba(255,255,255,.8); }
		.rk-kpi--warn .rk-kpi__value { color: var(--bad); }
		.rk-kpi__label { font-size: .72rem; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; }
		.rk-kpi__value { font-size: 1.3rem; font-weight: 700; margin-top: .15rem; font-variant-numeric: tabular-nums; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
		.rk-card { background: var(--card); border: 1px solid var(--line); border-radius: 10px; padding: .9rem 1rem; margin-bottom: 1rem; }
		.rk-card__head { display: flex; justify-content: space-between; align-items: baseline; gap: .5rem; flex-wrap: wrap; margin-bottom: .5rem; }
		.rk-card__head h3 { font-size: .95rem; font-weight: 600; margin: 0; }
		.rk-table { width: 100% !important; font-size: .85rem; }
		.rk-table thead th { color: var(--muted); font-weight: 600; font-size: .72rem; text-transform: uppercase; letter-spacing: .03em; white-space: nowrap; border-bottom: 2px solid var(--line) !important; }
		.rk-table td, .rk-table th { padding: .45rem .5rem !important; vertical-align: middle !important; text-align: left !important; }
		.rk-table td { white-space: normal !important; }
		.rk-table .num, .rk-table .text-nowrap { text-align: right !important; font-variant-numeric: tabular-nums; white-space: nowrap !important; }
		.rk-table .text-nowrap { text-align: left !important; }
		.rk-table tfoot th { border-top: 2px solid var(--line) !important; color: var(--ink); font-size: .85rem; }
		.rk-table .btn-sm { padding: .15rem .45rem; }
		.rk-bad { color: var(--bad); font-weight: 600; }
		.rk-notice { background: #fff8e6; border: 1px solid #f3dfa8; border-radius: 8px; padding: .7rem .9rem; margin-bottom: 1rem; font-size: .86rem; }
		.rk-bar { height: 6px; background: #eef1f4; border-radius: 3px; overflow: hidden; min-width: 60px; }
		.rk-bar > span { display: block; height: 100%; background: var(--accent); border-radius: 3px; }
		.rk-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0 1rem; align-items: start; }
		@media (min-width: 1700px) { .rk-grid { grid-template-columns: minmax(0, 3fr) minmax(280px, 1fr); } }
		.rk-cats { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: .6rem 1.5rem; }
		@media (min-width: 1700px) { .rk-cats { grid-template-columns: minmax(0, 1fr); } }
		.rk-side { order: -1; }
		@media (min-width: 1700px) { .rk-side { order: 2; } }
		.rk-sort { display: inline-flex; gap: .3rem; }
		@media (max-width: 767px) {
			.rk-field, .rk-field--range { flex: 1 1 100%; min-width: 0; }
			.rk-actions { margin-left: 0; width: 100%; }
			.rk-actions .btn { flex: 1; }
			.rk-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
		}
	</style>
@endpush
@push('footer')
	<script src="{{asset('plugins/js/pickers/flatpickr/flatpickr.min.js')}}"></script>
	<script>
		$(function () {
			document.querySelectorAll('.rk-range').forEach(el =>
				flatpickr(el, {mode: 'range', dateFormat: 'Y-m-d', altInput: true, altFormat: 'd-M-Y'}))
		})
	</script>
@endpush
@endonce
