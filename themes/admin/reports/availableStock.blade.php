@extends('admin.layouts.admin')

@section('page-title','Manage Available / Sold')
@section('heading','Manage Available / Sold')
@section('breadcrumbs', 'Available / Sold ')

@section('content')
	<div class="card" id="section-overview">
		<div
			class="card-header bg-teal-400 header-elements-inline">
			<h6 class="card-title"> Available / Sold Stock</h6>
		</div>

		<div class="card-body">
			<div class="table-responsive">
				<table id="stockAvailability" class="table table-bordered table-striped table-hover">
					<thead>
					<tr>
						<th> Product</th>
						<th>Total Stock</th>
						<th>Available Stock</th>
						<th>Sold Stock</th>
						<th>Return Stock</th>
					</tr>
					</thead>
					<tfoot>
					<tr>
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
@include('plugins.ajax')
@include('plugins.DataTables')
@push('footer')
	<script>
	 let table
	 $(document).ready(function () {
		 table = $('#stockAvailability').dataTable({
			 processing: true,
			 serverSide: true,
			 autoWidth: true,
			 ajax: {
				 url: "{{route('reports.available-stock.ajax')}}",
				 type: 'post'
			 },
			 columns: [
				 { data: 'name' },
				 { data: 'totalStock' },
				 { data: 'StockChecking' },
				 { data: 'stockSold' },
				 { data: 'stockReturn' }

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

				 for (i = 1; i <= 4; i++) {

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
						'Rs.' + pageTotal + ' (Total Rs. ' + total + ')'
					 )
				 }

			 }
		 })

	 })


	</script>

@endpush
