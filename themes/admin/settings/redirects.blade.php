@extends('admin.layouts.admin')

@section('page-title','Role based Redirection')
@section('heading','Role based Redirection')
@section('sub-heading','Redirect User to specific location based on Role(s)')
@section('breadcrumbs', 'Redirections')
@section('content')
	<div class="card">
		<div
			class="card-header bg-teal-400 header-elements-inline">
			<h6 class="card-title">Redirections</h6>

		</div>

		<div class="card-body">
			<form method="post" action="{{route('setup.redirects.update')}}" id="form-update" class="form-horizontal">
				<div class="form-group row">
					@foreach($roles as $role)

						<div class="col-lg-6">
							<label for="role_{{$role->id}}" class="col-lg-12 col-form-label">
								<b>{{$role->name}}</b>
							</label>

							<input type="text"
							       name="redirect[{{$role->id}}]"
							       id="role_{{$role->id}}"
							       class="form-control"
							       value="{{$current[$role->id]}}"
							>
						</div>

					@endforeach
				</div>
				<br>
				<div class="box-footer text-right">
					<div class="btn-group">
						<input type="submit" name="saveSettings" id="save-settings" class="btn btn-primary"
						       value="Update">
					</div>
				</div>

				<br>
				<div
					class="card-header bg-teal-400 header-elements-inline">
					<h6 class="card-title">Help</h6>

				</div>
				<table class="table table-bordered table-striped">
					<thead>
					<tr>
						<td>Route</td>
						<td>Description</td>
					</tr>
					</thead>
					<tbody>
					<tr>
						<th>/</th>
						<th>Redirect to Front Home Page</th>
					</tr>
					<tr>
						<th>admin</th>
						<th>Redirect to Dashboard</th>
					</tr>
					<tr>
						<th>admin/pos</th>
						<th>Redirect to Point of Sale</th>
					</tr>
					</tbody>
				</table>

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
			 let $form = $('#form-update')
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
