{{--
	Page header for list/report pages: eyebrow, the range as the title, a note line,
	and the date presets (each keeps the page's other filters).
	@include('admin.partials.report-head', [
		'eyebrow' => 'Orders', 'range' => DateRange, 'presets' => [...],
		'params' => [ current filters, without range ], 'note' => 'By sale date · …',
	])
--}}
@php
	$params = array_filter( $params ?? [], fn( $v ) => $v !== '' && $v !== null && $v !== 0 );
@endphp
<div class="rk-head">
	<div>
		<div class="rk-eyebrow">{{ $eyebrow }}</div>
		<h2>{{ $range->label() }}</h2>
		@if(!empty($note))
			<div class="rk-muted">{{ $note }}</div>
		@endif
	</div>
	<div class="rk-presets">
		@foreach($presets as $label => $value)
			<a href="?{{ http_build_query( array_merge( $params, [ 'range' => $value ] ) ) }}"
			   class="rk-chip {{ $value === $range->value() ? 'is-active' : '' }}">{{ $label }}</a>
		@endforeach
	</div>
</div>
