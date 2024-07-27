@extends('admin.layouts.admin')

@section('page-title','Stock Inventory')
@section('heading','Stock Inventory')
@section('breadcrumbs', 'Stock Inventory')

@section('content')
	<form method="post" action="#" name="form-cart" id="form-cart" class="form-horizontal">
		@csrf
		<input type="hidden" name="vendor_id" id="vendor_id" value="{{$vendor->id}}">
		<input type="hidden" id="cart_url" value="{{route('inventory.show_cart',$vendor->id)}}">
		<div class="col-md-6">
			@include('admin.pos.products')
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
				@include('admin.inventory.products')
			</div>
			<div class="col-md-4">
				@include('admin.inventory.payment')
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
	 })

	 ui.$body.on('click', '[data-action="addCart"]', function (e) {
		 e.preventDefault()
		 let $el = $(this)
		 let id = $el.data('id')
		 let product = $el.data('name')
		 let pprice = $el.data('pprice')
		 let stock = $('.stock' + id).val()
		 let url = '{{route("inventory.add_to_cart", $vendor->id) }}'
		 $.ajax({
			 url: url,
			 type: 'post',
			 dataType: 'json',
			 data: {
				 'id': id,
				 'purchase_price': pprice,
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

	 ui.$body.on('click', '[data-action="emptyCart"]', function (e) {
		 e.preventDefault()
		 $.confirm({
			 title: 'Empty Cart?',
			 content: 'Do you want to Empty this Cart?',
			 type: 'red',
			 buttons: {
				 confirm: function () {
					 $.ajax({
						 url: '{{route('inventory.empty_cart', $vendor->id)}}',
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
		 let table = $('#table-inventory-product')
		 let td = table.find('tbody > tr').children('td')
		 let input = td.find('.stock' + id)
		 input.val(parseInt(input.val()) + 1)

	 })

	 ui.$body.on('click', '[data-action="minus"]', function (e) {
		 e.preventDefault()
		 let $el = $(this)
		 let id = $el.data('id')
		 let table = $('#table-inventory-product')
		 let td = table.find('tbody > tr').children('td')
		 let input = td.find('.stock' + id)
		 input.val(parseInt(input.val()) - 1)

	 })

	 function totalAmount () {
		 let sumVal = 0
		 $('#table-inventory-cart > tbody > tr').each(function () {
			 let self = $(this).find(':input')
			 console.log(self)
			 let sum = parseFloat(self.eq(0).val()) * parseFloat(self.eq(1).val())
			 sumVal = parseFloat(sumVal) + parseFloat(sum)

		 })

		 $('#price').val(parseFloat(sumVal))
		 proceed()
	 }

	 function round (value, precision) {
		 let aPrecision = Math.pow(10, precision)
		 return Math.round(value * aPrecision) / aPrecision
	 }

	 function proceed () {
		 let discount = $('#disc').val()
		 let dis = $('#discount').val()
		 let price = $('#price').val()
		 let delivery_charges = $('#delivery').val()
		 let total
		 if (discount === 'per') {
			 let amountDisc = (parseFloat(price) * parseFloat(dis)) / 100
			 total = (parseFloat(price) - parseFloat(delivery_charges) - parseFloat(amountDisc))
		 } else {
			 total = parseFloat(price) - parseFloat(delivery_charges) - parseFloat(dis)
		 }
		 $('#netAmount').val(round(parseFloat(total), 2))
		 $('#paid').val(round(parseFloat(total), 2))
	 }

	 function deleteRow (id) {
		 $.confirm({
			 title: 'Delete?',
			 content: 'Do you want to delete this product?',
			 type: 'red',
			 buttons: {
				 confirm: function () {
					 $.ajax({
						 url: '{{route('inventory.delete_from_cart', $vendor->id)}}',
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

	 function updateCart (id) {
		 let url = '{{route("inventory.add_to_cart", $vendor->id) }}'
		 let stock = $('.stockCart' + id).val()
		 let price = $('.priceCart' + id).val()
		 $.ajax({
			 url: url,
			 type: 'post',
			 dataType: 'json',
			 data: {
				 'id': id,
				 'price': price,
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

	 ui.$body.on('click', '[data-action="save"]', function (e) {
		 e.preventDefault()
		 let products = []
		 $('#table-inventory-cart tr').each(function (row, tr) {
				if ($(tr).find('td:eq(0)').text() == '') {
				} else {
					let sub = {
						'id': $(tr).find('td:eq(0)').text(),
						'stock': $(tr).find('td:eq(2)').find('input').val(),
						'pprice': $(tr).find('td:eq(3)').find('input').val(),
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
					 let invoice_no = $('#invoice_no').val()
					 let paid = $('#paid').val()
					 if (products.length > 0) {
						 $.ajax({
							 url: '{{route('inventory.add',$vendor->id)}}',
							 type: 'post',
							 dataType: 'json',
							 data: {
								 products: products,
								 invoice_no: invoice_no,
								 sub_total: price,
								 total: netAmount,
								 discount: discount,
								 date: date,
								 payment: price,
								 delivery_charges: delivery_charges,
								 amount: paid
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


	</script>
@endpush()
