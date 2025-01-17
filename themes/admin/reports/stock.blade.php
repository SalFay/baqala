@extends('admin.layouts.admin')

@section('page-title','Manage Orders')
@section('heading','Manage Orders')
@section('breadcrumbs', 'Orders')

@section('content')
	<div class="card" id="section-overview">
		<div
			class="card-header bg-teal-400 header-elements-inline">
			<h6 class="card-title"> Total Stock</h6>
		</div>

		<div class="card-body">
			<div class="table-responsive">
				<table id="stockTable" class="table table-bordered table-striped table-hover">
					<thead>
					<tr>
						<th>Order #</th>
						<th>Vendor</th>
						<th>Invoice #</th>
						<th>Sub Total</th>
						<th>Discount</th>
						<th>Delivery</th>
						<th>Net Amount</th>
						<th>Credit</th>
						<th>Debit</th>
						<th>Date</th>

					</tr>
					</thead>
					<tbody>
					@foreach($stock as $r)
						<tr>
							<td>{{$r->id}}</td>
							<td>{{$r->vendor->name}}</td>
							<td>{{$r->invoice_no}}</td>
							<td>{{$r->sub_total}}</td>
							<td>{{$r->discount}}</td>
							<td>{{$r->delivery_charges}}</td>
							<td>{{$r->total}}</td>
							<td>{{$r->credit}}</td>
							<td>{{$r->debit}}</td>
							<td>{{date_format($r->created_at, 'j F, Y, g:i a')}}</td>

						</tr>
					@endforeach

					</tbody>
				</table>
			</div>
		</div>
	</div>
@endsection

@include('plugins.DataTables')
@push('footer')
	<script>
	 let table
	 $(document).ready(function () {

		 table = $('#stockTable').dataTable({
             "lengthMenu": [[100, "All", 50, 25], [100, "All", 50, 25]],

             autoWidth: true,
			 ordering: false,
			 search: {
				 'regex': true
			 }
		 })

	 })


	</script>

@endpush
