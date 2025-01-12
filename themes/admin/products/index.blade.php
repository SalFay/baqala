@extends('admin.layouts.admin')

@section('page-title','Manage Products')
@section('heading','Manage Products')
@section('breadcrumbs', 'Products')

@section('content')
	@include('admin.products.edit')
	@include('admin.products.overview')
@endsection
@include('plugins.ajax')
@include('plugins.DataTables')
@include('plugins.select2')
@push('footer')
	{!! $dataTable->scripts() !!}
	<script>
	 $(document).ready(function () {
		 onScan.attachTo(document, {
			 scanButtonKeyCode: false,
			 scanButtonLongPressTime: 500,
			 timeBeforeScanTest: 200,
			 avgTimeByChar: 40,
			 suffixKeyCodes: [13],

			 onScan: function (sScanned) { // Alternative to document.addEventListener('scan')
				 let barcode = sScanned
				 $('#barcode').val(barcode)
			 },

			 onKeyDetect: function (iKeyCode) { // output all potentially relevant key events - great for debugging!
				 console.log('Pressed: ' + iKeyCode)
			 }

		 })
		 // Remove onScan.js from a DOM element completely
		 onScan.detachFrom(document)
		 $('#categories, #categoryFilter').select2({
			 ajax: {
				 url: "{{route('select2.categories')}}",
				 type: 'post',
				 data: function (params) {
					 return {
						 term: params.term
					 }
				 }
			 }
		 })

		 $('#filter-form').on('submit', function (e) {
			 e.preventDefault()
			 window.LaravelDataTables['products-table'].ajax.reload()
		 })
		 $('#reset-filters').on('click', function () {
			 setTimeout(function () {
				 window.LaravelDataTables['products-table'].ajax.reload()
			 }, 50)
			 $('.filter-row').hide()
		 })

	 })

	 $('#barcode').keypress(function (event) {
		 if (event.which == '10' || event.which == '13') {
			 event.preventDefault()
		 }
	 })
	 // Delete
	 ui.$body.on('click', '[data-action="delete"]', function (e) {
		 e.preventDefault()
		 let $el = $(this)
		 $.confirm({
			 title: 'Delete?',
			 content: 'Do you want to delete this product?',
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
								 window.LaravelDataTables['products-table'].ajax.reload()
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
		 resetProductForm()
		 let $el = $(this)
		 let $form = $('#form-edit')
		 $form.attr('action', $el.attr('data-url'))
		 $('#form-submit').removeAttr('disabled').val('Add Product')
		 $('#section-edit').find('.card-title').text('Add Product')
		 ui.toggleSection('section-edit', 'section-overview')
		 $('#barcode').val('').focus()

	 })

	 ui.$body.on('submit', '#form-edit', function (e) {
		 e.preventDefault()
		 let $form = $(this)
		 $('#form-submit').attr('disabled', true).html('Uploading...')
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
					 resetProductForm()
					 ui.toggleSection('section-overview', 'section-edit')
					 return true
				 }
				 $('#form-submit').removeAttr('disabled')
				 ui.errorMessage(res.message)
			 },
			 error: function (res) {
				 $('#form-submit').removeAttr('disabled')
				 ui.ajaxError(res)
			 }
		 })
	 })

	 ui.$body.on('click', '[data-action="edit"]', function (e) {
		 e.preventDefault()
		 let $form = $('#form-edit')
		 let $el = $(this)
		 resetProductForm()
		 $.ajax({
			 url: $el.attr('data-url'),
			 dataType: 'json',
			 success: function (res) {
				 $('#name').val(res.name)
				 $('#arabic_name').val(res.arabic_name)
				 $('#purchase_price').val(res.purchase_price)
				 $('#sale_price').val(res.taxable_price)
				 $('#barcode').val(res.pid)
				 $('#status').val(res.status)
				 if (res.taxable === 'Yes') {
					 $('#taxable').prop('checked', true)
				 } else {
					 $('#taxable').prop('checked', false)

				 }
				 let editCategory = ''
				 if (res.category !== null) {
					 editCategory = new Option(res.category.name, res.category.id, false, false)
					 $('#categories').append(editCategory).trigger('change')
				 }

				 $('#form-submit').removeAttr('disabled').val('Update Product')
				 $('#section-edit').find('.card-title').text('Edit Product')

				 $form.attr('action', $el.attr('data-url'))
				 ui.toggleSection('section-edit', 'section-overview')

			 },
			 error: function (res) {
				 ui.ajaxError(res)
			 }
		 })

	 })

	 function resetProductForm () {
		 $('#categories').empty().trigger('change')
		 window.LaravelDataTables['products-table'].ajax.reload()
		 $('#form-edit')[0].reset()
	 }

	 ui.$body.on('click', '#form-cancel', function (e) {
		 e.preventDefault()
		 resetProductForm()
		 ui.toggleSection('section-overview', 'section-edit')
	 })

	 $('body').on('click', '#filter-btn', function () {
		 $('#filter-btn').addClass('show-filter')
		 $('.filter-row').show()
	 })

	 $('body').on('click', '.show-filter', function () {
		 $('#filter-btn').removeClass('show-filter')
		 $('.filter-row').hide()
	 })


	</script>
@endpush()
