<div class="card" id="section-overview">
	<div
		class="card-header bg-teal-400 header-elements-inline">
		<h6 class="card-title">All Products</h6>

		<div class="header-elements">
			<div class="list-icons">

				<a href="#" data-url="{{route('products.add')}}" class="btn btn-warning btn-xs" data-action="add">
					<i class="fas fa-plus"></i> Add Product
				</a>
				<a href="{{route('products.print')}}" class="btn btn-info btn-xs" data-action="print">
					<i class="fas fa-barcode"></i> Print Product Barcodes
				</a>
				<button type="button" id="filter-btn"
				        class="btn btn-primary waves-effect">
					<i class="fas fa-filter"></i>Filter
				</button>
			</div>
		</div>
	</div>

	<div class="card-body">
		<div class="filter-row" style="display:none">
			@include('admin.products.filter')
		</div>

		<br>
		{!! $dataTable->table() !!}

	</div>
</div>

