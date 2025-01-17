@extends('admin.layouts.admin')

@section('page-title','Manage Orders Item')
@section('heading','Manage Orders Item')
@section('breadcrumbs', 'Orders Item')

@section('content')
	<div class="card" id="section-overview">
		<div
			class="card-header bg-teal-400 header-elements-inline">
			<h6 class="card-title"> Margin / Profit</h6>
		</div>

		<div class="card-body">
			<div class="table-responsive">
				<table id="orderItemTable" class="table table-bordered table-striped table-hover">
					<thead>
					<tr>
						<th> Order #</th>
						<th> Product</th>
						<th>Stock</th>
						<th>Cost</th>
						<th>Total Cost</th>
						<th>Sale</th>
						<th>Total Sale</th>
						<th>Profit / Item</th>
						<th>Profit</th>
						<th>Discount</th>
						<th>Net Profit</th>
						<th>Status</th>
						<th>Date</th>

					</tr>
					</thead>
					<tbody>
					@foreach($orders as $order)
       <?php
       $discount = \App\Models\Order::where( 'id', $order->id )->sum( 'discount' );
       $profit = ( $order->sale_price - $order->purchase_price ) * $order->stock;
       ?>
							<tr>
								<td>{{$order->order_id}}</td>
								<td>{{$order->product->full_name}}</td>
								<td>{{$order->stock}}</td>
								<td>{{$order->purchase_price}}</td>
								<td>{{$order->stock * $order->purchase_price}}</td>
								<td>{{$order->sale_price}}</td>
								<td>{{$order->stock * $order->sale_price}}</td>
								<td>{{($order->sale_price - $order->purchase_price)}}</td>
								<td>{{$profit}}</td>
								<td>{{$discount}}</td>
								<td>{{$profit - $discount}}</td>

								@if($order->status === 'Pending')
									<td><span class="badge bg-primary">Pending</span></td>
								@elseif($order->status === 'Delivered')
									<td><span class="badge bg-success">Delivered</span></td>

								@else
									<td><span class="badge bg-danger">Return</span></td>

								@endif
								<td>{{date_format($order->created_at, 'j F, Y, g:i a')}}</td>

							</tr>
					@endforeach

					</tbody>
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

@include('plugins.DataTables')
@push('footer')
	<script>
	 let table
	 $(document).ready(function () {

		 table = $('#orderItemTable').dataTable({
			 'footerCallback': function (row, data, start, end, display) {
				 var api = this.api(), data

				 // Remove the formatting to get integer data for summation
				 let intVal = function (i) {
					 return typeof i === 'string' ?
						i.replace(/[\$,]/g, '') * 1 :
						typeof i === 'number' ?
						 i : 0
				 }

				 for (i = 3; i <= 10; i++) {

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

			 },
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
