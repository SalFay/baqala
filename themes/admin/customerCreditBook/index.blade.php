@extends('admin.layouts.admin')

@section('page-title','Manage Customer Credit Book')
@section('heading','Manage Customer Credit Book')
@section('breadcrumbs', 'Customer Credit Book')

@section('content')
	@include('admin.customerCreditBook.edit')
	@include('admin.customerCreditBook.overview')
@endsection
@include('plugins.ajax')
@include('plugins.DataTables')
@include('plugins.select2')
@push('footer')
	<script>
	 let table
	 $(document).ready(function () {

		 $('#customer').select2({
			 ajax: {
				 url: "{{route('select2.customers')}}",
				 type: 'post',
				 data: function (params) {
					 return {
						 term: params.term
					 }
				 }
			 },
		 })

		 table = $('#table-overview').dataTable({
			 autoWidth: true,
			 ordering: false,
			 ajax: {
				 url: "{{route('customerCredit.ajax')}}",
				 type: 'post'
			 },
			 columns: [
				 { data: 'id' },
				 { data: 'customer', name: 'customer.first_name' },
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
				 $('#customerWrapper').hide()
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
		 $('#customer').empty().trigger('change')
		 $('#form-edit')[0].reset()
		 $('#customerWrapper').show()
	 }

	 ui.$body.on('click', '#form-cancel', function (e) {
		 e.preventDefault()
		 resetCreditBookForm()
		 ui.toggleSection('section-overview', 'section-edit')
	 })


	</script>
@endpush()
