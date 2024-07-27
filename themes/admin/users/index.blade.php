@extends('admin.layouts.admin')

@section('page-title','Manage Users')
@section('heading','Manage Users')
@section('breadcrumbs', 'Users')

@section('content')
	@include('admin.users.edit')
	@include('admin.users.overview')
@endsection
@include('plugins.ajax')
@include('plugins.DataTables')
@include('plugins.select2')
@push('footer')
	<script>
	 let table
	 $('.manager').hide()
	 $(document).ready(function () {

		 $('#roles').select2({
			 ajax: {
				 url: "{{route('select2.roles')}}",
				 type: 'post',
				 data: function (params) {
					 return {
						 term: params.term
					 }
				 }
			 }
		 })

		 table = $('#table-users').dataTable({
			 autoWidth: false,
			 processing: true,
			 serverSide: true,
			 ajax: {
				 url: "{{route('users.ajax')}}",
				 type: 'post'
			 },
			 columns: [
				 { data: 'first_name' },
				 { data: 'last_name' },
				 { data: 'email' },
				 { data: 'user_roles' },
				 { data: 'verified' },
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
			 content: 'Do you want to delete this user?',
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
		 resetUserForm()
		 let $el = $(this)
		 let $form = $('#form-edit')
		 $form.attr('action', $el.attr('data-url'))
		 $('#form-submit').val('Add User')
		 $('#section-edit').find('.card-title').text('Add User')
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
					 resetUserForm()
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
		 resetUserForm()
		 $.ajax({
			 url: $el.attr('data-url'),
			 dataType: 'json',
			 success: function (res) {
				 $('#first_name').val(res.first_name)
				 $('#last_name').val(res.last_name)
				 $('#email').val(res.email)
				 $('#status').val(res.status)
				 $('#phone').val(res.phone)
				 let editRole = ''
				 if (res.role !== null) {
					 editRole = new Option(res.role.name, res.role.id, false, false)
					 $('#roles').append(editRole).trigger('change')
				 }

				 $('#form-submit').removeAttr('disabled').val('Update User')
				 $('#section-edit').find('.card-title').text('Edit User')

				 $form.attr('action', $el.attr('data-url'))
				 ui.toggleSection('section-edit', 'section-overview')

			 },
			 error: function (res) {
				 ui.ajaxError(res)
			 }
		 })

	 })

	 ui.$body.on('click', '[data-action="verify"]', function (e) {
		 e.preventDefault()
		 let $el = $(this)

		 $.ajax({
			 url: $el.attr('data-url'),
			 type: 'put',
			 dataType: 'json',
			 success: function (res) {
				 ui.successMessage(res.message)
				 table.api().ajax.reload(null, false)
			 },
			 error: function (res) {
				 ui.ajaxError(res)
			 }
		 })

	 })

	 function resetUserForm () {
		 $('#roles').empty().trigger('change')
		 $('#form-edit')[0].reset()
	 }

	 ui.$body.on('click', '#form-cancel', function (e) {
		 e.preventDefault()
		 resetUserForm()
		 ui.toggleSection('section-overview', 'section-edit')
	 })


	</script>
@endpush()
