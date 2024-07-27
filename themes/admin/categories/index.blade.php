@extends('admin.layouts.admin')

@section('page-title','Manage Categories')
@section('heading','Manage Categories')
@section('breadcrumbs', 'Categories')

@section('content')
	@include('admin.categories.edit')
	@include('admin.categories.overview')
@endsection

@include('plugins.ajax')
@include('plugins.DataTables')

@push('footer')
	<script>
	 let table
	 $(document).ready(function () {
		 table = $('#table-category').dataTable({
			 autoWidth: true,
			 ordering: false,
			 ajax: {
				 url: "{{route('categories.ajax')}}",
				 type: 'post'
			 },
			 columns: [
				 { data: 'name' },
				 { data: 'code' },
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
			 content: 'Do you want to delete this code?',
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
		 let $el = $(this)
		 let $form = $('#form-edit')
		 $form.attr('action', $el.attr('data-url'))
		 $('#form-submit').removeAttr('disabled').val('Add Category')
		 $('#section-edit').find('.card-title').text('Add Category')
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
					 $form[0].reset()
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

		 $.ajax({
			 url: $el.attr('data-url'),
			 dataType: 'json',
			 success: function (res) {
				 $('#name').val(res.name)
				 $('#code').val(res.code)

				 $('#form-submit').removeAttr('disabled').val('Update Category')
				 $('#section-edit').find('.card-title').text('Edit ' + res.name + ' Category')

				 $form.attr('action', $el.attr('data-url'))
				 ui.toggleSection('section-edit', 'section-overview')

			 },
			 error: function (res) {
				 ui.ajaxError(res)
			 }
		 })

	 })

	 ui.$body.on('click', '#form-cancel', function (e) {
		 e.preventDefault()
		 $('#form-edit')[0].reset()
		 ui.toggleSection('section-overview', 'section-edit')
	 })


	</script>
@endpush()
