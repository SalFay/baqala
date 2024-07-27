@extends('admin.layouts.admin')

@section('page-title','Manage Inventory Log')
@section('heading','Manage Inventory Log')
@section('breadcrumbs', 'Inventory Log')

@section('content')
	<div class="card" id="section-overview">
		<div
			class="card-header bg-teal-400 header-elements-inline">
			<h6 class="card-title"> Total Inventory Log</h6>
		</div>

		<div class="card-body">
			<div class="table-responsive">
				@include('admin.dateRange')

				<table id="inventoryTable" class="table table-bordered table-striped table-hover">
					<thead>
					<tr>
						<th>Order #</th>
						<th>Order Type</th>
						<th>Product</th>
						<th>Stock</th>
						<th>Cost</th>
						<th>Status</th>
						<th>Date</th>

					</tr>
					</thead>
					<tbody>


					</tbody>
				</table>
			</div>
		</div>
	</div>
@endsection
@include('plugins.ajax')
@include('plugins.date-picker')

@include('plugins.DataTables')
@push('footer')
	<script>
	 let table = $('#inventoryTable')
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
				 url: "{{route('reports.inventory.ajax')}}",
				 type: 'post',
				 data: { date: date }
			 },
			 columns: [
				 { data: 'id' },
				 { data: 'type' },
				 { data: 'product' },
				 { data: 'stock' },
				 { data: 'cost' },
				 { data: 'status' },
				 { data: 'date' }

			 ],
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
