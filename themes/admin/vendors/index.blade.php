@extends('admin.layouts.admin')

@section('page-title','Manage Vendors')
@section('heading','Manage Vendors')
@section('breadcrumbs', 'Vendors')

@section('content')
	@include('admin.vendors.edit')
	@include('admin.vendors.overview')
@endsection
@include('plugins.ajax')
@include('plugins.DataTables')
@include('plugins.select2')
@push('footer')
	<script>
	 let table
	 $(document).ready(function () {

		 table = $('#table-vendors').dataTable({
			 'footerCallback': function (row, data, start, end, display) {
				 var api = this.api(), data

				 // Remove the formatting to get integer data for summation
				 let intVal = function (i) {
					 return typeof i === 'string' ?
						i.replace(/[\$,]/g, '') * 1 :
						typeof i === 'number' ?
						 i : 0
				 }

				 for (i = 3; i <= 3; i++) {

					 // Total over all pages
					 total = api
						.column(i)
						.data()
						.reduce(function (a, b) {
							var t = intVal(a) + intVal(b)
							return t.toFixed(3)
						}, 0)

					 // Total over this page
					 pageTotal = api
						.column(i, { page: 'current' })
						.data()
						.reduce(function (a, b) {
							var t = intVal(a) + intVal(b)
							return t.toFixed(3)
						}, 0)

					 // Update footer
					 $(api.column(i).footer()).html(
						'Rs.' + pageTotal + ' (Total Rs. ' + total + ')'
					 )
				 }

			 },

			 autoWidth: false,
			 processing: true,
			 serverSide: true,
			 ajax: {
				 url: "{{route('vendors.ajax')}}",
				 type: 'post'
			 },
			 columns: [
				 { data: 'name' },
				 { data: 'mobile' },
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
			 content: 'Do you want to delete this vendor?',
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
		 resetVendorForm()
		 let $el = $(this)
		 let $form = $('#form-edit')
		 $form.attr('action', $el.attr('data-url'))
		 $('#form-submit').removeAttr('disabled').val('Add Vendor')
		 $('#section-edit').find('.card-title').text('Add Vendor')
		 ui.toggleSection('section-edit', 'section-overview')
	 })

	 ui.$body.on('submit', '#form-edit', function (e) {
		 e.preventDefault()
		 $('#form-submit').attr('disabled', true).html('Uploading...')
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
					 resetVendorForm()
					 table.api().ajax.reload(null, false)
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
		 resetVendorForm()
		 $.ajax({
			 url: $el.attr('data-url'),
			 dataType: 'json',
			 success: function (res) {
				 $('#name').val(res.name)
				 $('#mobile').val(res.mobile)
				 $('#address').val(res.address)
				 $('#status').val(res.status)

				 $('#form-submit').removeAttr('disabled').val('Update Vendor')
				 $('#section-edit').find('.card-title').text('Edit Vendor')

				 $form.attr('action', $el.attr('data-url'))
				 ui.toggleSection('section-edit', 'section-overview')

			 },
			 error: function (res) {
				 ui.ajaxError(res)
			 }
		 })

	 })

	 function resetVendorForm () {
		 $('#form-edit')[0].reset()
	 }

	 ui.$body.on('click', '#form-cancel', function (e) {
		 e.preventDefault()
		 resetVendorForm()
		 ui.toggleSection('section-overview', 'section-edit')
	 })


	</script>
@endpush()
