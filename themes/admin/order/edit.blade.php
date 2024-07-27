@extends('admin.layouts.admin')

@section('page-title','Sale')
@section('heading','Sale')
@section('breadcrumbs', 'Sale')

@section('content')
	<form method="post" action="#" name="form-cart" id="form-cart" class="form-horizontal">
		@csrf
		<input type="hidden" name="order_id" id="order_id" value="{{$order->id}}">
		<input type="hidden" id="cart_url" value="{{route('orders.show_cart',$order->id)}}">

		<div class="form-group row">

			<div class="col-md-6">
				@include('admin.pos.products')
			</div>
			<div class="col-md-6">

				<label><strong>Product Name:</strong></label>
				<select name="products" id="products" data-placeholder="Select Product"
				        class="form-control select2"></select>
			</div>
		</div>
		<br>
		<div id="tables" class="row">
			<div class="col-md-8">
				<div class="card border-dark">
					<div class="card-header bg-dark text-white header-elements-inline">
						<h6 class="card-title">Cart</h6>
						<button type="button" data-action="emptyCart"
						        class="btn btn-success">Empty Cart
						</button>
					</div>
					<div class="card-body" id="cart">

					</div>
				</div>

				{{--	@include('admin.order.products')--}}
			</div>
			<div class="col-md-4">

				<div class="card border-dark">
					<div class="card-header bg-dark text-white header-elements-inline">
						<h6 class="card-title">Payment Method</h6>
						<div class="header-elements">
							<button type="button" style="float:right;bottom: 5px;" data-action="save" id="saveBtn"
							        class="btn btn-success">
								Update
							</button>
						</div>
					</div>
					<div class="card-body">
						<div class="form-group row">
							<label for="date" class="col-sm-5 col-form-label">Customer Name:</label>
							<div class="col-sm-7">
								<input type="text" name="customer_name" value="{{$order->customer ? $order->customer->full_name : $order->customer_name}}" id="customer_name"
								       class="form-control"/>
							</div>
						</div>
						<div class="form-group row">
							<label for="date" class="col-sm-5 col-form-label">Cashier Name:</label>
							<div class="col-sm-7">
								<input type="text" name="cashier_name" value="{{$order->cashier_name}}" id="cashier_name"  class="form-control"/>
							</div>
						</div>
						<div class="form-group row">
							<label for="date" class="col-sm-5 col-form-label">Date:</label>
							<div class="col-sm-7">
								<input type="date" name="date" value="{{$order->date}}" id="date" required class="form-control"/>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-5 col-form-label">Total:</label>
							<div class="col-sm-7">
								<input type="text" name="price" id="price" value="{{$order->price}}" readonly class="form-control"
								       placeholder="Total"/>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-5 col-form-label">Delivery Charges:</label>
							<div class="col-sm-7">
								<input id="delivery" value="{{$order->delivery_charges}}" onblur="proceed()" type="text"
								       name="delivery_charges" class="form-control">
							</div>
						</div>

						<div class="form-group row">
							<label class="col-sm-5 col-form-label">Discount:</label>
							<div class="col-sm-7">
								<input id="discount" value="{{$order->discount}}" onblur="proceed()" type="text"
								       name="discount"
								       class="form-control">
							</div>
						</div>

						{{--<label class="col-sm-5 col-form-label">Vat Amount:</label>
						<input type="text" name="vatAmount" id="vatAmount" value="{{option('vat_amount')}}%" readonly class="form-control"
									 placeholder="Net Amount"/>--}}

						<div class="form-group row">
							<label class="col-sm-5 col-form-label">Net Amount:</label>
							<div class="col-sm-7">
								<input type="text" name="netAmount" value="{{$order->netAmount}}" id="netAmount" readonly
								       class="form-control"
								       placeholder="Net Amount"/>

							</div>
						</div>

						<div class="form-group row">
							<label class="col-sm-5 col-form-label">Paid:</label>
							<div class="col-sm-7">
								<input type="text" name="paid" value="{{$order->paid}}" id="paid" onchange="changed()"
								       class="form-control"
								       placeholder="Enter Paid Amount"/>

							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-5 col-form-label">Change:</label>
							<div class="col-sm-7">
								<input type="text" name="change" id="change" value="0" class="form-control"/>


							</div>

						</div>
					</div>
				</div>
				@push('head')
					<style>
         .form-group {
             margin-bottom: 0.25rem !important;
         }
					</style>
				@endpush


			</div>

		</div>

	</form>
@endsection
@include('plugins.ajax')
@include('plugins.DataTables')
@include('plugins.select2')
@push('footer')

	<script>


	 $(document).ready(function () {

		 refreshCart()
		 $('body').addClass('sidebar-xs')
		 $('#payments').select2({
			 ajax: {
				 url: "{{route('select2.banks')}}",
				 type: 'post',
				 data: function (params) {
					 return {
						 term: params.term
					 }
				 }
			 },
		 })

	 })

	 ui.$body.on('click', '[data-action="addCart"]', function (e) {
		 e.preventDefault()
		 let $el = $(this)
		 let id = $el.data('id')
		 let product = $el.data('name')
		 let price = $el.data('price')
		 let taxable_price = $el.data('actual')

		 let purchase_price = $el.data('purchase_price')
		 let stock = $('.stock' + id).val()
		 let url = '{{route("orders.add_to_cart", $order->id) }}'
		 $.ajax({
			 url: url,
			 type: 'post',
			 dataType: 'json',
			 data: {
				 'id': id,
				 'sale_price': price,
				 'taxable_price': taxable_price,
				 'purchase_price': purchase_price,
				 'stock': stock
			 },
			 success: function (res) {
				 if (res.status === 'ok') {
					 ui.successMessage(res.message)
					 refreshCart()
					 return true
				 }
				 ui.errorMessage(res.message)
			 },
			 error: function (res) {
				 ui.ajaxError(res)

			 }
		 })
	 })

	 function updateCart (id) {
		 let url = '{{route("orders.add_to_cart", $order->id) }}'
		 let stock = $('.stockCart' + id).val()
		 let taxable_price = $('.priceCart' + id).val()
		 let price = $('.actualCart' + id).val()
		 let purchase_price = $('.pPriceCart' + id).val()
		 $.ajax({
			 url: url,
			 type: 'post',
			 dataType: 'json',
			 data: {
				 'id': id,
				 'price': price,
				 'taxable_price': taxable_price,
				 'purchase_price': purchase_price,
				 'stock': stock
			 },
			 success: function (res) {
				 if (res.status === 'ok') {
					 refreshCart()
					 return true
				 }
				 ui.errorMessage(res.message)
			 },
			 error: function (res) {
				 ui.ajaxError(res)
			 }
		 })

	 }

	 ui.$body.on('click', '[data-action="emptyCart"]', function (e) {
		 e.preventDefault()
		 $.confirm({
			 title: 'Empty Cart?',
			 content: 'Do you want to Empty this Cart?',
			 type: 'red',
			 buttons: {
				 confirm: function () {
					 $.ajax({
						 url: '{{route('orders.empty_cart', $order->id)}}',
						 type: 'post',
						 dataType: 'json',
						 success: function (res) {
							 if (res.status === 'ok') {
								 ui.successMessage(res.message)
								 refreshCart()
								 return true
							 }
							 ui.errorMessage(res.message)
						 },
						 error: function (res) {
							 ui.ajaxError(res)
						 }
					 })
				 }
			 }
		 })
	 })

	 ui.$body.on('click', '[data-action="plus"]', function (e) {
		 e.preventDefault()
		 let $el = $(this)
		 let id = $el.data('id')
		 let table = $('#table-order-product')
		 let td = table.find('tbody > tr').children('td')
		 let input = td.find('.stock' + id)
		 input.val(parseInt(input.val()) + 1)

	 })

	 ui.$body.on('click', '[data-action="minus"]', function (e) {
		 e.preventDefault()
		 let $el = $(this)
		 let id = $el.data('id')
		 let table = $('#table-order-product')
		 let td = table.find('tbody > tr').children('td')
		 let input = td.find('.stock' + id)
		 input.val(parseInt(input.val()) - 1)

	 })

	 function totalAmount () {
		 let netAmount = 0
		 let sumPrice = 0
		 $('#table-cart > tbody > tr').each(function () {
			 let self = $(this).find(':input')
			 netAmount += parseFloat(self.eq(1).val()) * parseFloat(self.eq(2).val())
			 sumPrice += parseFloat(self.eq(1).val()) * parseFloat(self.eq(0).val())
		 })

		 $('#price').val(parseFloat(sumPrice))
		 $('#netAmount').val(parseFloat(netAmount))
		 proceed()

	 }

	 function round (value, precision) {
		 let aPrecision = Math.pow(10, precision)
		 return Math.round(value * aPrecision) / aPrecision
	 }

	 function proceed () {
		 let discount = $('#disc').val()
		 let dis = $('#discount').val()
		 //let vat = '{{option('vat_amount')}}'
		 let price = $('#netAmount').val()
		 let delivery_charges = $('#delivery').val()
		 // let amountVat = (parseFloat(price) * parseFloat(vat)) / 100
		 let total
		 if (discount === 'per') {
			 let amountDisc = (parseFloat(price) * parseFloat(dis)) / 100
			 total = (parseFloat(price) + parseFloat(delivery_charges) - parseFloat(amountDisc))
		 } else {
			 total = parseFloat(price) + parseFloat(delivery_charges) - parseFloat(dis)
		 }
		 //total += parseFloat(amountVat)
		 $('#netAmount').val(round(parseFloat(total), 2))
		 $('#paid').val(round(parseFloat(total), 2))
		 changed()
	 }

	 function changed () {
		 let paid = $('#paid').val()
		 let netAmount = $('#netAmount').val()
		 let change = paid - netAmount
		 $('#change').val(round(change, 2))
	 }

	 function deleteRow (id) {
		 $.confirm({
			 title: 'Delete?',
			 content: 'Do you want to delete this product?',
			 type: 'red',
			 buttons: {
				 confirm: function () {
					 $.ajax({
						 url: '{{route('orders.delete_from_cart', $order->id)}}',
						 type: 'post',
						 dataType: 'json',
						 data: {
							 'id': id
						 },
						 success: function (res) {
							 if (res.status === 'ok') {
								 ui.successMessage(res.message)
								 refreshCart()
								 return true
							 }
							 ui.errorMessage(res.message)
						 },
						 error: function (res) {
							 ui.ajaxError(res)
						 }
					 })
				 },
				 cancel: function () {
				 }
			 }
		 })
	 }

	 key('s', function () {
			let products = []
			$('#table-cart tr').each(function (row, tr) {
				 if ($(tr).find('td:eq(0)').text() == '') {
				 } else {
					 let sub = {
						 'id': $(tr).find('td:eq(0)').text(),
						 'sale_price': $(tr).find(':input').eq(0).val(),
						 'stock': $(tr).find('td:eq(2)').find('input').val(),
						 'taxable_price': $(tr).find('td:eq(3)').find('input').val(),
						 'purchase_price': $(tr).find('input[type=hidden]').val(),
					 }
					 products.push(sub)
				 }
			 }
			)

			let price = $('#price').val()
			let delivery_charges = $('#delivery').val()
			let discount = $('#discount').val()
			let netAmount = $('#netAmount').val()
			let date = $('#date').val()
			let amount = $('#paid').val()
			let payment_id = $('#payments').val()
			let payment_type = 'Cash'
			if (products.length > 0) {
				$.ajax({
					url: '{{route('orders.update',$order->id)}}',
					type: 'post',
					dataType: 'json',
					data: {
						products: products,
						sub_total: price,
						total: netAmount,
						discount: discount,
						date: date,
						payment_id: payment_id,
						payment: price,
						delivery_charges: delivery_charges,
						amount: amount,
						payment_type: payment_type,
						customer_name: $('#customer_name').val(),
						cashier_name: $('#cashier_name').val()
					},
					success: function (res) {
						if (res.status === 'ok') {
							ui.successMessage(res.message)
							window.location = res.url
							return true
						}
						ui.errorMessage(res.message)
					},
					error: function (res) {
						ui.ajaxError(res)
					}
				})
			} else {
				ui.errorMessage('Enter Stock to Proceed')
			}
		}
	 )

	 ui.$body.on('click', '[data-action="save"]', function (e) {
		 e.preventDefault()
		 let products = []
		 $('#table-cart tr').each(function (row, tr) {
				if ($(tr).find('td:eq(0)').text() == '') {
				} else {
					let sub = {
						'id': $(tr).find('td:eq(0)').text(),
						'sale_price': $(tr).find(':input').eq(0).val(),
						'stock': $(tr).find('td:eq(2)').find('input').val(),
						'taxable_price': $(tr).find('td:eq(3)').find('input').val(),
						'purchase_price': $(tr).find('input[type=hidden]').val(),
					}
					products.push(sub)
				}
			}
		 )

		 $.confirm({
			 title: 'Are you sure?',
			 content: 'You won\'t be able to revert this!',
			 type: 'warning',
			 buttons: {
				 confirm: function () {
					 let price = $('#price').val()
					 let delivery_charges = $('#delivery').val()
					 let discount = $('#discount').val()
					 let netAmount = $('#netAmount').val()
					 let date = $('#date').val()
					 let amount = $('#paid').val()
					 let payment_id = $('#payments').val()
					 let payment_type = 'Cash'
					 if (products.length > 0) {
						 $.ajax({
							 url: '{{route('orders.update',$order->id)}}',
							 type: 'post',
							 dataType: 'json',
							 data: {
								 products: products,
								 sub_total: price,
								 total: netAmount,
								 discount: discount,
								 date: date,
								 payment_id: payment_id,
								 payment: price,
								 delivery_charges: delivery_charges,
								 amount: amount,
								 payment_type: payment_type,
					customer_name: $('#customer_name').val(),
					cashier_name: $('#cashier_name').val()
							 },
							 success: function (res) {
								 if (res.status === 'ok') {
									 ui.successMessage(res.message)
									 window.location = res.url
									 return true

								 }
								 console.log(res)
								 ui.errorMessage(res.message)
							 },
							 error: function (res) {
								 console.log(res)
								 ui.ajaxError(res)
							 }
						 })

					 } else {
						 ui.errorMessage('Enter Stock to Proceed')
					 }

				 },
				 cancel: function () {

				 }
			 }
		 })

	 })

	 function refreshCart () {

		 $.ajax({
			 url: $('#cart_url').val(),
			 type: 'patch',
			 success: function (res) {
				 $('#cart').html(res)
				 totalAmount()
			 }
		 })
	 }

	 $('#products').select2({
		 ajax: {
			 url: "{{route('select2.products')}}",
			 type: 'post',
			 data: function (params) {
				 return {
					 term: params.term
				 }
			 }
		 },
	 }).on('change', function () {
		 let id = $(this).val()
		 let order_id = {!! $order->id !!}
		 $.ajax({
			 url: '{{route('products.get')}}',
			 type: 'post',
			 dataType: 'json',
			 data: { id: id, order: order_id },
			 success: function (res) {
				 if (res.status === 'ok') {
					 ui.successMessage(res.message)
					 refreshCart()
					 return true
				 }
				 ui.errorMessage(res.message)
			 },
			 error: function (res) {
				 ui.ajaxError(res)
			 }
		 })
	 })

	</script>
@endpush()
