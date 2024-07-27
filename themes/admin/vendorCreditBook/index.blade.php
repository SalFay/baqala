@extends('admin.layouts.admin')

@section('page-title','Manage Vendor Credit Book')
@section('heading','Manage Vendor Credit Book')
@section('breadcrumbs', 'Vendor Credit Book')

@section('content')
	@include('admin.vendorCreditBook.edit')
	@include('admin.vendorCreditBook.overview')
@endsection
@include('plugins.ajax')
@include('plugins.DataTables')
@include('plugins.select2')
@push('footer')
	<script>
	 let table
	 $(document).ready(function () {

		 $('#vendor').select2({
			 ajax: {
				 url: "{{route('select2.vendors')}}",
				 type: 'post',
				 data: function (params) {
					 return {
						 term: params.term
					 }
				 }
			 },
		 })

		 table = $('#table-overview').dataTable({
			 autoWidth: false,
			 processing: true,
			 serverSide: true,
			 ajax: {
				 url: "{{route('vendorCredit.ajax')}}",
				 type: 'post'
			 },
			 columns: [
				 { data: 'id' },
				 { data: 'vendor', name: 'vendor.first_name' },
				 { data: 'total' },
				 { data: 'credit' },
				 { data: 'debit' },
				 { data: 'date' },
				 { data: 'action' }
			 ],
			 search: {
				 'regex': true
			 }
		 })
	 })

	 // Delete
	 ui.$body.on('click', '[data-action="delete"]', function (e) {
		 e.preventDefault()
		 let $el = $(this)
		 $.confirm({
			 title: 'Delete?',
			 content: 'Do you want to delete this Payment?',
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
		 resetCreditBookForm()
		 let $el = $(this)
		 let $form = $('#form-edit')
		 $form.attr('action', $el.attr('data-url'))
		 $('#form-submit').val('Add Credit Book')
		 $('#section-edit').find('.card-title').text('Add Credit Book')
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
					 resetCreditBookForm()
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
		 resetCreditBookForm()
		 $.ajax({
			 url: $el.attr('data-url'),
			 dataType: 'json',
			 success: function (res) {
				 $('#vendorWrapper').hide()
				 $('#credit').val(res.credit)
				 $('#debit').val(res.debit)
				 $('#comments').val(res.comments)
				 $('#form-submit').val('Update Credit Book')
				 $('#section-edit').find('.card-title').text('Edit Credit Book')

				 $form.attr('action', $el.attr('data-url'))
				 ui.toggleSection('section-edit', 'section-overview')

			 },
			 error: function (res) {
				 ui.ajaxError(res)
			 }
		 })

	 })

	 function resetCreditBookForm () {
		 $('#vendor').empty().trigger('change')
		 $('#form-edit')[0].reset()
		 $('#vendorWrapper').show()
	 }

	 ui.$body.on('click', '#form-cancel', function (e) {
		 e.preventDefault()
		 resetCreditBookForm()
		 ui.toggleSection('section-overview', 'section-edit')
	 })


	</script>
@endpush()
