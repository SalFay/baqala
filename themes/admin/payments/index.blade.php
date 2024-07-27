@extends('admin.layouts.admin')

@section('page-title','Manage Payment Methods')
@section('heading','Manage Payment Methods')
@section('breadcrumbs', 'Payment Methods')

@section('content')
	@include('admin.payments.edit')
	@include('admin.payments.overview')
@endsection
@include('plugins.ajax')
@include('plugins.select2')
@push('footer')
	<script>

	 $(document).ready(function () {
		 $('#source').on('change', function () {
			 let source = $('#source').val()
			 if (source === 'Cash') {
				 $('#cashMsg').show()
				 $('#advance').hide()
			 } else {
				 $('#cashMsg').hide()
				 $('#advance').show()
			 }
		 })
	 })

	 // Add
	 ui.$body.on('click', '[data-action="add"]', function (e) {
		 e.preventDefault()
		 let $el = $(this)
		 let $form = $('#form-edit')
		 $form.attr('action', $el.attr('data-url'))
		 $('#form-submit').removeAttr('disabled').val('Add Payment Method')
		 $('#section-job-edit').find('.card-title').text('Add Payment Method')
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
					 location.reload()
					 return true
				 }
				 $('#form-submit').removeAttr('disabled')
				 ui.errorMessage(res.message)
			 },
			 error: function (res) {
				 $('#form-submit').removeAttr('disabled')
				 ui.ajaxError(res, 0)
			 }
		 })
	 })

	 ui.$body.on('click', '[data-action="edit"]', function (e) {
		 e.preventDefault()
		 let $form = $('#form-edit')
		 let $el = $(this)
		 resetPaymentForm()
		 $.ajax({
			 url: $el.attr('data-url'),
			 dataType: 'json',
			 success: function (res) {
				 $('#name').val(res.name)
				 $('#account_number').val(res.account_number)
				 $('#account_title').val(res.account_title)
				 $('#account_branch').val(res.account_branch)
				 $('#source').val(res.source).trigger('change')
				 $('#form-submit').removeAttr('disabled').val('Update Payment Method')
				 $('#section-edit').find('.card-title').text('Update Payment Method')
				 $form.attr('action', $el.attr('data-url'))
				 ui.toggleSection('section-edit', 'section-overview')
			 },
			 error: function (res) {
				 ui.ajaxError(res, 0)
			 }
		 })

	 })

	 function resetPaymentForm () {
		 $('#form-edit')[0].reset()
	 }

	 ui.$body.on('click', '#form-cancel', function (e) {
		 e.preventDefault()
		 resetPaymentForm()
		 window.location.reload()
		 ui.toggleSection('section-overview', 'section-edit')
	 })

	 // Delete
	 ui.$body.on('click', '[data-action="delete"]', function (e) {
		 e.preventDefault()
		 let $el = $(this)
		 $.confirm({
			 title: 'Delete?',
			 content: 'Do you want to delete this Payment Method?',
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
								 location.reload()
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
	 })


	</script>
@endpush()
