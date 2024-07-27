@extends('admin.layouts.admin')

@section('page-title','General Settings')
@section('heading','General Settings')
@section('sub-heading','')
@section('breadcrumbs', 'General Settings')
@include('plugins.ajax')
@include('plugins.select2')
@section('content')
	<div class="card">
		<div
			class="card-header bg-teal-400 header-elements-inline">
			<h6 class="card-title">General Settings</h6>

		</div>

		<div class="card-body">
			<form method="post" action="{{route('setup.general.update')}}" id="form-update" class="form-horizontal"
			      enctype="multipart/form-data">
				<div class="form-group row">


					<div class="col-lg-6">
						<label for="title" class="col-lg-12 col-form-label">
							<b>Site Title</b>
						</label>

						<input type="text"
						       name="title"
						       id="title"
						       class="form-control"
						       value="{{$data['title']}}"
						>
					</div>

					<div class="col-lg-6">
						<label for="title" class="col-lg-12 col-form-label">
							<b>Address</b>
						</label>

						<input type="text"
						       name="address"
						       id="address"
						       class="form-control"
						       value="{{$data['address']}}"
						>
					</div>


				</div>
				<div class="form-group row">


					<div class="col-lg-6">
						<label for="title" class="col-lg-12 col-form-label">
							<b>Mobile</b>
						</label>

						<input type="text"
						       name="mobile"
						       id="mobile"
						       class="form-control"
						       value="{{$data['mobile']}}"
						>
					</div>
					<div class="col-lg-6">
						<label for="vat_amount" class="col-lg-12 col-form-label">
							<b>VAT Amount </b>
						</label>

						<input type="number"
						       name="vat_amount"
						       id="vat_amount"
						       class="form-control"
						       value="{{$data['vat_amount']}}"
						>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-lg-6">
						<label for="vat_number" class="col-lg-12 col-form-label">
							<b>VAT Number </b>
						</label>

						<input type="number"
						       name="vat_number"
						       id="vat_number"
						       class="form-control"
						       value="{{$data['vat_number']}}"
						>
					</div>

					<div class="col-lg-6">
						<label for="printer" class="col-lg-12 col-form-label">
							Default Printer:</label>

						<select name="printer" id="printer" data-placeholder="Select Printer" class="form-control"
						        data-module="select2">
				<option value="Thermal Arabic New" {!! $data['printer'] === 'Thermal Arabic New'?' selected':'' !!}>Thermal
								Arabic New
							</option>
							<option value="Thermal Eng" {!! $data['printer'] === 'Thermal Eng'?' selected':'' !!}>Thermal English</option>
					</select>
					</div>


				</div>
				<div class="form-group row">
					<div class="col-lg-6">
						<img style="border: 1px solid" width="120px" height="120px" id="image"
						     src="{{asset('assets').'/'. $data['logo']}}">

						<label for="title" class="col-lg-12 col-form-label">
							<b>Logo</b>
						</label>

						<input type="file" name="file" class="form-control">
					</div>

				</div>

				<div class="box-footer text-right">
					<div class="btn-group">
						<input type="submit" name="saveSettings" id="save-settings" class="btn btn-primary"
						       value="Update">
					</div>
				</div>

			</form>
		</div>
	</div>


@endsection

@include('plugins.ajax')

@push('footer')
	<script>
	 $(document).ready(function () {
		 $('#form-update').on('submit', function (e) {
			 e.preventDefault()
			 let form = $(this)
			 let form_data = new FormData(form[0])
			 $.ajax({
				 url: form.attr('action'),
				 type: 'post',
				 data: form_data,
				 contentType: false,
				 processData: false,
				 dataType: 'json',
				 success: function (res) {
					 if (res.status === 'ok') {
						 ui.successMessage(res.message)
						 if (res.redirect) {
							 window.location.reload()
							 return
						 }
						 return true
					 }
					 ui.errorMessage(res.message)
				 },
				 error: function (res) {
					 ui.ajaxError(res)
				 }
			 })
		 })
	 })
	</script>
@endpush
