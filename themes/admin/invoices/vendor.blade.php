@extends('admin.layouts.admin')

@section('page-title','Manage Vendor Invoices')
@section('heading','Manage Vendor Invoices')
@section('breadcrumbs', 'Vendor Invoices')

@section('content')
	<div class="card" id="section-overview">
		<div
			class="card-header bg-teal-400 header-elements-inline">
			<h6 class="card-title">Vendor Invoices</h6>
		</div>

		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered table-striped table-hover" id="table-vendorInvoice">
					<thead>
					<tr>
						<th>Order No</th>
						<th>Vendor</th>
						<th>Invoice No</th>
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
		 table = $('#table-vendorInvoice').dataTable({
			 autoWidth: false,
			 processing: true,
			 serverSide: true,
			 ajax: {
				 url: "{{route('vendorAjax')}}",
				 type: 'post'
			 },
			 columns: [
				 { data: 'id' },
				 { data: 'vendor' },
				 { data: 'invoice_no' },
				 { data: 'payment_type' },
				 { data: 'total' },
				 { data: 'action' }

			 ]
		 })
	 })


	</script>
@endpush()
