<div class="card border-dark" id="productList">
	<div class="card-header bg-dark text-white header-elements-inline">
		<h6 class="card-title">Products List</h6>
	</div>
	<div class="card-body">

		<div class="table-responsive text-center">

			<table class="table table-bordered table-striped table-hover" id="table-inventory-product">
				<thead>
				<tr>
					<th>Name</th>
					<th>Purchase Price</th>
				</tr>
				</thead>
			</table>
		</div>
	</div>
</div>

@push('footer')

	<script>
	 let tableProduct
	 $(document).ready(function () {

		 tableProduct = $('#table-inventory-product').dataTable({
			 autoWidth: true,
			 ordering: false,
			 ajax: {
				 url: "{{route('inventories.products')}}",
				 type: 'post'
			 },
			 columns: [
				 { data: 'name' },
				 { data: 'purchase_price' },
			 ],
			 search: {
				 'regex': true
			 }
		 })
	 })
	</script>
@endpush
