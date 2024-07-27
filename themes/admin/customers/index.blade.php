@extends('admin.layouts.admin')

@section('page-title','Manage Customers')
@section('heading','Manage Customers')
@section('breadcrumbs', 'Customers')

@section('content')
	@include('admin.customers.edit')
	@include('admin.customers.overview')
@endsection
@include('plugins.ajax')
@include('plugins.DataTables')
@include('plugins.select2')
@push('footer')
	<script>
	 let table
	 $(document).ready(function () {

		 table = $('#table-customer').dataTable({
			 'footerCallback': function (row, data, start, end, display) {
				 var api = this.api(), data

				 // Remove the formatting to get integer data for summation
				 let intVal = function (i) {
					 return typeof i === 'string' ?
						i.replace(/[\$,]/g, '') * 1 :
						typeof i === 'number' ?
						 i : 0
				 }

				 for (i = 4; i <= 4; i++) {

					 // Total over all pages
					 total = api
						.column(i)
						.data()
						.reduce(function (a, b) {
							var t = intVal(a) + intVal(b)
							return t.toFixed(4)
						}, 0)

					 // Total over this page
					 pageTotal = api
						.column(i, { page: 'current' })
						.data()
						.reduce(function (a, b) {
							var t = intVal(a) + intVal(b)
							return t.toFixed(4)
						}, 0)

					 // Update footer
					 $(api.column(i).footer()).html(
						'Rs.' + pageTotal + ' (Total Rs. ' + total + ')'
					 )
				 }

			 },

			 autoWidth: true,
			 ordering: false,
			 ajax: {
				 url: "{{route('customers.ajax')}}",
				 type: 'post'
			 },
			 columns: [
				 { data: 'first_name' },
				 { data: 'last_name' },
				 { data: 'phone_work' },
				 { data: 'address' },
				 { data: 'debit' },
				 { data: 'status' },
				 { data: 'action' }
			 ]
		 })
	 })

	 // Delete
	 ui.$body.on('click', '[data-action="delete"]', function (e) {
		 e.preventDefault()
		 let $el = $(this)
		 $.confirm({
			 title: 'Delete?',
			 content: 'Do you want to delete this customer?',
			 type: 'red',
			 buttons: {
				 confirm: function () {
					 $.ajax({
						 url: $el.attr('data-url'),
						 type: 'post',
						 dataType: 'json',
						 success: function (res) {
							 if (res.status === 'ok') {
								 ui.successMessage(res.message)
								 table.api().ajax.reload(null, false)
								 return
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
	 })

	 // Add
	 ui.$body.on('click', '[data-action="add"]', function (e) {
		 e.preventDefault()
		 resetCustomerForm()
		 let $el = $(this)
		 let $form = $('#form-edit')
		 $form.attr('action', $el.attr('data-url'))
		 $('#form-submit').val('Add Customer')
		 $('#section-edit').find('.card-title').text('Add Customer')
		 ui.toggleSection('section-edit', 'section-overview')
	 })

	 ui.$body.on('submit', '#form-edit', function (e) {
		 e.preventDefault()
		 let $form = $(this)

		 $.ajax({
			 url: $form.attr('action'),
			 type: 'post',
			 dataType: 'json',
			 data: $form.serialize(),
			 success: function (res) {
				 if (res.status === 'ok') {
					 ui.successMessage(res.message)
					 if (res.redirect) {
						 window.location.reload()
						 return
					 }
					 resetCustomerForm()
					 table.api().ajax.reload(null, false)
					 ui.toggleSection('section-overview', 'section-edit')

					 return true
				 }
				 ui.errorMessage(res.message)
			 },
			 error: function (res) {
				 ui.ajaxError(res)
			 }
		 })
	 })

	 ui.$body.on('click', '[data-action="edit"]', function (e) {
		 e.preventDefault()
		 let $form = $('#form-edit')
		 let $el = $(this)
		 resetCustomerForm()
		 $.ajax({
			 url: $el.attr('data-url'),
			 dataType: 'json',
			 success: function (res) {
				 $('#status').val(res.status)
				 $('#first_name').val(res.first_name)
				 $('#last_name').val(res.last_name)
				 $('#business_name').val(res.business_name)
				 $('#billing_address').val(res.billing_address)
				 $('#billing_city').val(res.billing_city)
				 $('#billing_state').val(res.billing_state)
				 $('#billing_country').val(res.billing_country)
				 $('#billing_zipcode').val(res.billing_zipcode)
				 $('#shipping_address').val(res.shipping_address)
				 $('#shipping_city').val(res.shipping_city)
				 $('#shipping_state').val(res.shipping_state)
				 $('#shipping_zipcode').val(res.shipping_zipcode)
				 $('#shipping_country').val(res.shipping_country)
				 $('#phone_home').val(res.phone_home)
				 $('#phone_work').val(res.phone_work)
				 $('#phone_mobile').val(res.phone_mobile)
				 $('#phone_other').val(res.phone_other)
				 $('#email').val(res.email)
				 $('#address').val(res.address)
				 $('#form-submit').removeAttr('disabled').val('Update Customer')
				 $('#section-edit').find('.card-title').text('Edit Customer')

				 $form.attr('action', $el.attr('data-url'))
				 ui.toggleSection('section-edit', 'section-overview')

			 },
			 error: function (res) {
				 ui.ajaxError(res)
			 }
		 })

	 })

	 function resetCustomerForm () {
		 $('#form-edit')[0].reset()
	 }

	 ui.$body.on('click', '#form-cancel', function (e) {
		 e.preventDefault()
		 resetCustomerForm()
		 ui.toggleSection('section-overview', 'section-edit')
	 })

	 function shipInfo () {
		 if ($('#ship_address').prop('checked') == true) {

			 let billingAddress = $('#billing_address').val()
			 let billingCity = $('#billing_city').val()
			 let billingState = $('#billing_state').val()
			 let billingCountry = $('#billing_country').val()
			 let billingZipCode = $('#billing_zipcode').val()

			 $('#shipping_address').val(billingAddress)
			 $('#shipping_city').val(billingCity)
			 $('#shipping_state').val(billingState)
			 $('#shipping_zipcode').val(billingZipCode)
			 $('#shipping_country').val(billingCountry).trigger('change')

		 } else {
			 $('#shipping_address').val('')
			 $('#shipping_city').val('')
			 $('#shipping_state').val('')
			 $('#shipping_zipcode').val('')
			 $('#shipping_country').empty().trigger('change')
		 }
	 }

	</script>
@endpush()
