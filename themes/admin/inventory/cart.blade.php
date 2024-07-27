<div class="table-responsive text-center">
	<table id="table-inventory-cart" class="table table-striped table-bordered table-hover">
		<thead>
		<tr>
			<th>Sr No</th>
			<th>Name</th>
			<th>Stock</th>
			<th>Purchase Price</th>
			<th>Action</th>
		</tr>
		</thead>
		<tbody id="tbody">
		@foreach($cart as $key => $item)
			<tr class="p{{$key}}">
				<td>{{$key}}</td>
				<td>{{$item['product']->full_name}}</td>
				<td>
					<input type="number" style="text-align: center"
					       onblur="updateCart({{$key}})"
					       value="{{$item['stock']}}"
					       min="1"
					       class="form-control stockCart{{$key}}"
					       name="cart[{{$key}}][stock]"></td>
				<td>
					<input type="number" style="text-align: center"
					       value="{{$item['price']}}"
					       min="1"
					       onblur="updateCart({{$key}})"
					       class="form-control priceCart{{$key}}"
					       name="cart[{{$key}}][purchase_price]"></td>
				<td>
					<a class='red' href='#'
					   onclick='deleteRow({{$key}});'>
						<i class='ace-icon fa fa-trash bigger-130'></i></a>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
</div>

