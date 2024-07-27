@extends('admin.layouts.admin')

@section('page-title','Manage Customer Invoices')
@section('heading','Manage Customer Invoices')
@section('breadcrumbs', 'Customer Invoices')

@section('content')
	<div class="card" id="section-overview">
		<div
			class="card-header bg-teal-400 header-elements-inline">
			<h6 class="card-title">Customer Invoices</h6>
		</div>

		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered table-striped table-hover" id="table-customerInvoice">
					<thead>
					<tr>
						<th>Order No</th>
						<th>Customer</th>
						<th>Payment</th>
						<th>Total</th>
						<th>Action</th>
					</tr>
					</thead>

				</table>
			</div>
		</div>
	</div>

@endsection

@include('plugins.ajax')
@include('plugins.DataTables')

@push('footer')
	<script>
	 let table
	 $(document).ready(function () {
		 table = $('#table-customerInvoice').dataTable({
			 autoWidth: false,
			 processing: true,
			 serverSide: true,
			 ajax: {
				 url: "{{route('customerAjax')}}",
				 type: 'post'
			 },
			 columns: [
				 { data: 'id' },
				 { data: 'customer' },
				 { data: 'payment_type' },
				 { data: 'total' },
				 { data: 'action' }

			 ]
		 })
	 })

	 // Delete
	 ui.$body.on('click', '[data-action="delete"]', function (e) {
		 e.preventDefault()
		 let $el = $(this)
		 $.confirm({
			 title: 'Delete?',
			 content: 'Do you want to delete this Order?',
			 type: 'red',
			 buttons: {
				 confirm: function () {
					 $.ajax({
						 url: $el.attr('data-url'),
						 type: 'post',
						 dataType: 'json',
						 success: function (res) {
							 if (res.status === 'ok') {
								 ui.successMessage(res.message)
								 table.api().ajax.reload(null, false)
								 return
							 }
							 ui.errorMessage(res.message)
						 },
						 error: function (res) {
							 ui.ajaxError(res)
						 }
					 })
				 },
				 cancel: function () {
				 }
			 }
		 })
	 })


	</script>
@endpush()
