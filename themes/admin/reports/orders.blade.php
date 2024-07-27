@extends('admin.layouts.admin')

@section('page-title','Manage Orders')
@section('heading','Manage Orders')
@section('breadcrumbs', 'Orders')

@section('content')
	<div class="card" id="section-overview">
		<div
			class="card-header bg-teal-400 header-elements-inline">
			<h6 class="card-title"> Total Orders</h6>
		</div>

		<div class="card-body">
			<div class="table-responsive">
				@include('admin.dateRange')

				<table id="orderTable" class="table table-bordered table-striped table-hover">
					<thead>
					<tr>
						<th> Order #</th>
						<th> Customer</th>
						<th>Sub Total</th>
						<th>Discount</th>
						<th>Delivery</th>
						<th>VAT</th>
						<th>Net Amount</th>
						<th>Credit</th>
						<th>Debit</th>
						<th>Date</th>

					</tr>
					</thead>
					<tfoot>
					<tr>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
					</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>
@endsection
@include('plugins.date-picker')
@include('plugins.ajax')

@include('plugins.DataTables')
@push('footer')
	<script>
	 let table = $('#orderTable')

	 $(document).ready(function () {

		 callDataTable()

	 })

	 function callDataTable (date = '') {
		 table.dataTable({
			 autoWidth: true,
			 ordering: false,
			 processing: true,
			 serverSide: true,
			 ajax: {
				 url: "{{route('reports.order.ajax')}}",
				 type: 'post',
				 data: { date: date }
			 },
			 columns: [
				 { data: 'id' },
				 { data: 'customer' },
				 { data: 'sub_total' },
				 { data: 'discount' },
				 { data: 'delivery_charges' },
				 { data: 'vat' },
				 { data: 'total' },
				 { data: 'credit' },
				 { data: 'debit' },
				 { data: 'date' }

			 ],
			 'footerCallback': function (row, data, start, end, display) {
				 var api = this.api(), data

				 // Remove the formatting to get integer data for summation
				 let intVal = function (i) {
					 return typeof i === 'string' ?
						i.replace(/[\$,]/g, '') * 1 :
						typeof i === 'number' ?
						 i : 0
				 }

				 for (i = 2; i <= 8; i++) {

					 // Total over all pages
					 total = api
						.column(i)
						.data()
						.reduce(function (a, b) {
							var t = intVal(a) + intVal(b)
							return t.toFixed(2)
						}, 0)

					 // Total over this page
					 pageTotal = api
						.column(i, { page: 'current' })
						.data()
						.reduce(function (a, b) {
							var t = intVal(a) + intVal(b)
							return t.toFixed(2)
						}, 0)

					 // Update footer
					 $(api.column(i).footer()).html(
						'Rs.' + pageTotal
					 )
				 }

			 }
		 })
	 }

	 $('#filter-range').on('click', function (e) {
		 e.preventDefault()
		 $val = $('#dataRange').val()
		 table.DataTable().destroy()
		 callDataTable($val)
	 })

	 $('#filter-refresh').on('click', function (e) {
		 e.preventDefault()
		 $('#dataRange').val('')
		 table.DataTable().destroy()
		 callDataTable()
	 })


	</script>

@endpush

