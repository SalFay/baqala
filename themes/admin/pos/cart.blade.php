<div class="table-responsive text-center">
	<table id="table-cart" class="table table-condensed table-bordered">
		<thead>
		<tr>
			<th>Item No</th>
			<th>Name</th>
			<th>Stock</th>
			<th>Sale</th>
			<th>Purchase</th>
			<th>Action</th>
		</tr>
		</thead>
		<tbody id="tbody">
		@foreach(array_reverse($cart) as $key => $item)
			<tr class="p{{$item['id']}}">
				<input type="hidden"
				       value="{{$item['price']}}"
				       class="form-control actualCart{{$item['id']}}"
				       name="cart[{{$item['id']}}][sale_price]">
				<td>{{$item['id']}}</td>
				<td>
					@if($item['product']->arabic_name)
						{{$item['product']->arabic_name}}
					@else
						{{$item['product']->full_name}}
					@endif
				</td>
				<td>
					<input type="number" style="text-align: center; width: 80px"
					       onblur="updateCart({{$item['id']}})"
					       value="{{$item['stock']}}"
					       min="1"
					       class="form-control stockCart{{$item['id']}}"
					       name="cart[{{$item['id']}}][stock]"></td>
				<td style="    display: inline-flex;
    border: none;
    align-items: center;">
					<input type="number" style="text-align: center; width: 100px"
					       value="{{$item['taxable_price']}}"
					       oninput="updateCart({{$item['id']}})"
					       min="1"
					       class="form-control priceCart{{$item['id']}}"
					       name="cart[{{$item['id']}}][taxable_price]">
				</td>

				<td>
					<input type="number" style="text-align: center; width:100px"
					       value="{{$item['purchase_price']}}"
					       oninput="updateCart({{$item['id']}})"
					       min="1"
					       class="form-control pPriceCart{{$item['id']}}"
					       name="cart[{{$item['id']}}][purchase_price]"></td>
				<td>
					<a class='red' href='#'
					   onclick='deleteRow({{$item['id']}});'>
						<i class='ace-icon fa fa-trash bigger-130'></i></a>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
</div>