@extends('admin.layouts.admin')

@section('page-title','Manage Logs')
@section('heading','Logs')
@section('breadcrumbs')
	<li class="breadcrumb-item"><a href="{{route('admin.logs')}}">Users Timeline History</a>
	</li>
@endsection
@push('head')
	<link href="{{asset('assets/css/timeline.css')}}" type="text/css" rel="stylesheet">
	<style>
     .hidden {
         display: none !important;
     }
	</style>
@endpush

@section('content')
	@can('access auth history')
		<div class="card">
			<div class="card-header bg-teal-400 header-elements-inline">

				<div class="head-label">
					<h4>Users Timeline History</h4>

				</div>

			</div>

			<div class="card-body">

				<ul class="timeline">
					@include('admin.logs.timeline')
					<div class="card-footer">

						{!! $auth_logs->links() !!}

					</div>
				</ul>

			</div>

			@endcan


			@endsection
			@include('plugins.select2')

			@push('footer')
				<script>
			$(document).ready(function () {

				$('#filter-form').on('submit', function (e) {
					e.preventDefault()
					let $form = $(this).serialize()
					$.ajax({
						url: '{{route('admin.logs.filter')}}',
						type: 'post',
						dataType: 'json',
						data: $form,
						success: function (res) {
							if (res.status === 'ok') {
								$('.timeline').empty().append(res.view)
								return true
							}
							ui.errorMessage(res.message)
						},
						error: function (res) {
							ui.ajaxError(res, 0)
						}
					})
				})

				$('#reset-filters').on('click', function () {
					location.reload()
				})
			})

			$('body').on('click', '#filter-btn', function () {
				$('.filter-row').show()
			})
				</script>
		@endpush()
