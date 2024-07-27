@extends('admin.layouts.admin')
@section('content')
	<section id="basic-datatable">
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-header bg-teal-400 header-elements-inline">
						<h6 class="card-title">SMS History</h6>
						<div class="header-elements">
							@foreach(simplexml_load_string($smsHistory) as $data)
								@if($data->response > 0)
									<b> (Balance: {{$data->response}} - Expiry: {{$data->expiry}})</b> &nbsp;
								@endif
							@endforeach

						</div>

					</div>


					<div class="card-body">
						<br>
						{!! $dataTable->table() !!}
					</div>

				</div>
			</div>
		</div>
	</section>
@endsection
@include('plugins.select2')
@include('plugins.DataTables')
@push('footer')
	{!! $dataTable->scripts() !!}
	<script>
	 $(document).ready(function () {

		 $('#filter-form').on('submit', function (e) {
			 e.preventDefault()
			 window.LaravelDataTables['sms-table'].ajax.reload()
		 })
		 $('#reset-filters').on('click', function () {
			 setTimeout(function () {
				 window.LaravelDataTables['sms-table'].ajax.reload()
			 }, 50)
			 $('.filter-row').hide()
		 })

	 })

	 $('body').on('click', '#filter-btn', function () {
		 $('.filter-row').show()
	 })
	</script>
@endpush
