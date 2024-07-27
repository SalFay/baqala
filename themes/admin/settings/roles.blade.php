@extends('admin.layouts.admin')

@section('page-title','Setup Roles')
@section('heading','Setup Roles')
@section('breadcrumbs', 'Setup Roles')
@include('plugins.select2')
@section('content')
	@push('head')
		<style>
      .select2-container--default .select2-selection--multiple .select2-selection__choice {
          background-color: lightcoral;
      }
		</style>
	@endpush
	<div class="card">
		<div
			class="card-header bg-teal-400 header-elements-inline">
			<h6 class="card-title">Default Roles</h6>

		</div>

		<div class="card-body">
			<form method="post" action="{{route('setup.roles.update')}}" id="form-update">

				<table class="table table-bordered table-striped">
					<tbody>


					<tr>
						<td>
							<label for="managerRole"><strong>Manager Role</strong></label>
							<p class="text-muted">
								Default role for Manager.<br/>
								This role, and its permissions, will be applied
								to all Managers.
							</p>
						</td>
						<td class="column-form-control">
							<select name="managerRole" id="managerRole" data-placeholder="Select Role"
							        class="form-control"
							        data-module="select2">
								<option value="0">Select Role</option>
								@foreach($roles as $role)
									<option value="{{$role->id}}" {{$role->id==$oldRole['manager']?'Selected':''}}>
										{{$role->name}}
									</option>
								@endforeach
							</select>
						</td>
					</tr>

					<tr>
						<td>
							<label for="adminRole"><strong>Administrator Role</strong></label>
							<p class="text-muted">
								Default role for System Administrator.<br/>
								Users with this role will have all the permissions to access
								and use features of this system.
							</p>
						</td>
						<td class="column-form-control">
							<select name="adminRole" id="adminRole" data-placeholder="Select Role" class="form-control"
							        data-module="select2">
								<option value="0">Select Role</option>
								@foreach($roles as $role)
									<option value="{{$role->id}}" {{$role->id==$oldRole['admin']?'Selected':''}}>
										{{$role->name}}
									</option>
								@endforeach
							</select>
						</td>
					</tr>

					</tbody>
				</table>
				<br>
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
