@extends('admin.layouts.admin')

@section('page-title','Manage User Roles & Permissions')
@section('heading','Manage User Roles & Permissions')
@section('breadcrumbs', 'Roles & Permissions')

@section('content')
	@include('admin.roles.permissions')
	@include('admin.roles.edit')
	@include('admin.roles.overview')
@endsection

@include('plugins.ajax')
@include('plugins.DataTables')

@push('footer')
	<script>
	 let table
	 $(document).ready(function () {
		 table = $('#table-role').dataTable({
			 autoWidth: false,
			 processing: true,
			 serverSide: true,
			 ajax: {
				 url: "{{route('roles.ajax')}}",
				 type: 'post'
			 },
			 columns: [
				 { data: 'name' },
				 { data: 'description' },
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
			 content: 'Do you want to delete this role?',
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
		 $('#form-submit').removeAttr('disabled').val('Add Role')
		 $('#section-edit').find('.card-title').text('Add Role')
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

	 ui.$body.on('submit', '#form-permissions', function (e) {
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
					 $form[0].reset()
					 table.api().ajax.reload(null, false)
					 ui.toggleSection('section-overview', 'section-permissions')

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

		 $.ajax({
			 url: $el.attr('data-url'),
			 dataType: 'json',
			 success: function (res) {
				 $('#name').val(res.name)
				 $('#description').val(res.description)

				 $('#form-submit').removeAttr('disabled').val('Update Role')
				 $('#section-edit').find('.card-title').text('Edit ' + res.name + ' Role')

				 $form.attr('action', $el.attr('data-url'))
				 ui.toggleSection('section-edit', 'section-overview')

			 },
			 error: function (res) {
				 ui.ajaxError(res)
			 }
		 })

	 })

	 ui.$body.on('click', '[data-action="edit-permission"]', function (e) {
		 e.preventDefault()
		 let $form = $('#form-permissions')
		 let $el = $(this)

		 $.ajax({
			 url: $el.attr('data-url'),
			 success: function (res) {
				 $('#form-permissions').html(res)
				 $form.attr('action', $el.attr('data-url'))
				 ui.toggleSection('section-permissions', 'section-overview')

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

	 ui.$body.on('click', '#permissions-cancel', function (e) {
		 e.preventDefault()
		 $('#form-edit')[0].reset()
		 ui.toggleSection('section-overview', 'section-permissions')
	 })

	</script>
@endpush()
