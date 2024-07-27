@extends('admin.layouts.admin')

@section('page-title','Manage Credit / Debit')
@section('heading','Manage Credit / Debit')
@section('breadcrumbs', 'Credit / Debit')

@section('content')
	@include('admin.accounts.cash')
	@include('admin.accounts.transfer')
	@include('admin.accounts.expense')
	@include('admin.accounts.add')
	@include('admin.accounts.edit')
	@include('admin.accounts.overview')

@endsection

@include('plugins.ajax')
@include('plugins.DataTables')
@include('plugins.select2')
@include('plugins.date-picker')
@push('footer')
	<script>
	 let table = $('#table-account')

	 function callDataTable (date = '') {
		 table.dataTable({
			 autoWidth: true,
			 ordering: false,
			 ajax: {
				 url: "{{route('accounts.ajax')}}",
				 type: 'post',
				 data: { date: date }
			 },
			 columns: [
				 { data: 'date' },
				 { data: 'type' },
				 { data: 'name' },
				 { data: 'debit' },
				 { data: 'credit' },
				 { data: 'discount' },
				 { data: 'bank' },
				 { data: 'comments' },
				 { data: 'action' }
			 ],
			 'footerCallback': function (row, data, start, end, display) {
				 var api = this.api(), data

				 // Remove the formatting to get integer data for summation
				 let intVal = function (i) {
					 return typeof i === 'string' ?
						i.replace(/[\$,]/g, '') * 1 :
						typeof i === 'number' ?
						 i : 0
				 }

				 for (i = 3; i <= 5; i++) {

					 // Total over all pages
					 total = api
						.column(i)
						.data()
						.reduce(function (a, b) {
							var t = intVal(a) + intVal(b)
							return t.toFixed(2)
						}, 0)

					 // Total over this page
					 pageTotal = api
						.column(i, { page: 'current' })
						.data()
						.reduce(function (a, b) {
							var t = intVal(a) + intVal(b)
							return t.toFixed(2)
						}, 0)

					 // Update footer
					 $(api.column(i).footer()).html(
						'Rs.' + pageTotal + ' (Total Rs. ' + total + ')'
					 )
				 }

			 },

		 })
	 }

	 $(document).ready(function () {
		 callDataTable()
		 $('.banks').select2({
			 ajax: {
				 url: "{{route('select2.banks')}}",
				 type: 'post',
				 data: function (params) {
					 return {
						 term: params.term
					 }
				 }
			 },
			 tags: true
		 })

		 $('.expenses').select2({
			 ajax: {
				 url: "{{route('select2.expense')}}",
				 type: 'post',
				 data: function (params) {
					 return {
						 term: params.term
					 }
				 }
			 },
			 tags: true
		 })

		 $('#status').on('change', function () {
				let val = $('#status').val()
				if (val === 'Customer') {
					$('#vendorWrapper').hide()
					$('#customerWrapper').show()
					$('#vendors').removeAttr('required')
					$('#customers').select2({
						ajax: {
							url: "{{route('select2.customers')}}",
							type: 'post',
							data: function (param) {
								return {
									term: param.term
								}
							}
						}
					}).attr('required', true)
				}
				if (val === 'Vendor') {
					$('#customerWrapper').hide()
					$('#vendorWrapper').show()
					$('#customers').removeAttr('required')
					$('#vendors').select2({
						ajax: {
							url: "{{route('select2.vendors')}}",
							type: 'post',
							data: function (param) {
								return {
									term: param.term
								}
							}
						}
					}).attr('required', true)
				}

			}
		 )
	 })

	 // Add
	 ui.$body.on('click', '[data-action="add"]', function (e) {
		 e.preventDefault()
		 resetPaymentForm()
		 let $el = $(this)
		 let $form = $('#form-add')
		 $form.attr('action', $el.attr('data-url'))
		 $('#form-submit-add').removeAttr('disabled').val('Add Payment')
		 $('#section-add').find('.card-title').text('Add Payment')
		 $('#status').trigger('change')
		 ui.toggleSection('section-add', 'section-overview')
	 })

	 ui.$body.on('click', '[data-action="add-expense"]', function (e) {
		 e.preventDefault()
		 let $form = $('#form-expense')
		 resetPaymentForm()
		 let $el = $(this)
		 $form.attr('action', $el.attr('data-url'))
		 $('#form-submit-expense').removeAttr('disabled').val('Add Expense')
		 $('#section-expense').find('.card-title').text('Add Expense')
		 ui.toggleSection('section-expense', 'section-overview')
	 })

	 ui.$body.on('click', '[data-action="add-cash"]', function (e) {
		 e.preventDefault()
		 let $form = $('#form-cash')
		 resetPaymentForm()
		 let $el = $(this)
		 $form.attr('action', $el.attr('data-url'))
		 $('#form-submit-cash').removeAttr('disabled').val('Add Cash')
		 $('#section-cash').find('.card-title').text('Add Cash')
		 ui.toggleSection('section-cash', 'section-overview')
	 })

	 ui.$body.on('click', '[data-action="add-transfer"]', function (e) {
		 e.preventDefault()
		 let $form = $('#form-transfer')
		 resetPaymentForm()
		 let $el = $(this)
		 $form.attr('action', $el.attr('data-url'))
		 $('#form-submit-transfer').removeAttr('disabled').val('Add Transfer')
		 $('#section-transfer').find('.card-title').text('Add Transfer')
		 ui.toggleSection('section-transfer', 'section-overview')
	 })

	 ui.$body.on('submit', '#form-add', function (e) {
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
					 resetPaymentForm()
					 table.api().ajax.reload(null, false)
					 ui.toggleSection('section-overview', 'section-add')
					 return true
				 }
				 ui.errorMessage(res.message)
			 },
			 error: function (res) {
				 ui.ajaxError(res)
			 }
		 })

	 })

	 ui.$body.on('submit', '#form-expense', function (e) {
		 e.preventDefault()
		 let $form = $(this)
		 $('#form-submit-expense').attr('disabled', true).html('Uploading...')

		 $.ajax({
			 url: $form.attr('action'),
			 type: 'post',
			 dataType: 'json',
			 data: $form.serialize(),
			 success: function (res) {
				 if (res.status === 'ok') {
					 ui.successMessage(res.message)
					 table.api().ajax.reload(null, false)
					 ui.toggleSection('section-overview', 'section-expense')
					 return true
				 }
				 $('#form-submit-expense').removeAttr('disabled')

				 ui.errorMessage(res.message)
			 },
			 error: function (res) {
				 $('#form-submit-expense').removeAttr('disabled')

				 ui.ajaxError(res)
			 }
		 })

	 })

	 ui.$body.on('submit', '#form-cash', function (e) {
		 e.preventDefault()
		 let $form = $(this)
		 $('#form-submit-cash').attr('disabled', true).html('Uploading...')
		 $.ajax({
			 url: $form.attr('action'),
			 type: 'post',
			 dataType: 'json',
			 data: $form.serialize(),
			 success: function (res) {
				 if (res.status === 'ok') {
					 ui.successMessage(res.message)
					 table.api().ajax.reload(null, false)
					 ui.toggleSection('section-overview', 'section-cash')
					 return true
				 }
				 $('#form-submit-cash').removeAttr('disabled')

				 ui.errorMessage(res.message)
			 },
			 error: function (res) {
				 $('#form-submit-cash').removeAttr('disabled')
				 ui.ajaxError(res)
			 }
		 })

	 })

	 ui.$body.on('submit', '#form-transfer', function (e) {
		 e.preventDefault()
		 let $form = $(this)
		 $('#form-submit-transfer').attr('disabled', true).html('Uploading...')
		 $.ajax({
			 url: $form.attr('action'),
			 type: 'post',
			 dataType: 'json',
			 data: $form.serialize(),
			 success: function (res) {
				 if (res.status === 'ok') {
					 ui.successMessage(res.message)
					 table.api().ajax.reload(null, false)
					 ui.toggleSection('section-overview', 'section-transfer')
					 return true
				 }
				 $('#form-submit-transfer').removeAttr('disabled')

				 ui.errorMessage(res.message)
			 },
			 error: function (res) {
				 $('#form-submit-transfer').removeAttr('disabled')
				 ui.ajaxError(res)
			 }
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
				 $('#debit').val(res.debit)
				 $('#credit').val(res.credit)
				 $('#comments').val(res.comments)
				 let editBank = ''
				 if (res.bank !== null) {
					 editBank = new Option(res.bank.name, res.bank.id, false, false)
					 $('.banks').append(editBank).trigger('change')
				 }
				 $('#cheque').val(res.cheque)
				 $('#form-submit').removeAttr('disabled').val('Update Payment')
				 $('#section-edit').find('.card-title').text('Edit Payment')

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
		 $('.banks').empty().trigger('change')
		 $('#form-edit')[0].reset()
		 ui.toggleSection('section-overview', 'section-edit')
	 })

	 ui.$body.on('click', '#form-cancel-add', function (e) {
		 e.preventDefault()
		 resetPaymentForm()
		 ui.toggleSection('section-overview', 'section-add')
	 })
	 ui.$body.on('click', '#form-cancel-cash', function (e) {
		 e.preventDefault()
		 resetPaymentForm()
		 ui.toggleSection('section-overview', 'section-cash')
	 })
	 ui.$body.on('click', '#form-cancel-transfer', function (e) {
		 e.preventDefault()
		 resetPaymentForm()
		 ui.toggleSection('section-overview', 'section-transfer')
	 })
	 ui.$body.on('click', '#form-cancel-expense', function (e) {
		 e.preventDefault()
		 resetPaymentForm()
		 ui.toggleSection('section-overview', 'section-expense')
	 })

	 function resetPaymentForm () {
		 $('#customers').empty().trigger('change')
		 $('#vendors').empty().trigger('change')
		 $('.banks').empty().trigger('change')
		 $('.expenses').empty().trigger('change')
		 $('#form-add')[0].reset()
		 $('#form-cash')[0].reset()
		 $('#form-transfer')[0].reset()
		 $('#form-expense')[0].reset()

	 }

	 $('#filter-range').on('click', function (e) {
		 e.preventDefault()
		 $val = $('#dataRange').val()
		 table.DataTable().destroy()
		 callDataTable($val)
	 })

	 $('#filter-refresh').on('click', function (e) {
		 e.preventDefault()
		 $('#dataRange').val('')
		 table.DataTable().destroy()
		 callDataTable()
	 })


	</script>
@endpush()
