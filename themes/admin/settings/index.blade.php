@extends('admin.layouts.admin')

@section('page-title','Manage Settings')
@section('heading','Manage Settings')
@section('breadcrumbs', 'Settings')
@section('content')
	<div class="card">
		<div
			class="card-header bg-teal-400 header-elements-inline">
			<h6 class="card-title">Global Settings</h6>

		</div>

		<div class="card-body row">
			<div class="col-sm-6">

				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Users, Roles and Permissions</h3>
					</div>
					<table class="table">
						<tbody>
						<tr>
							<td>
								<a href="{{route('setup.general')}}"><strong>General Settings</strong></a>
								<br/>
								<p>
									General Setting of software
								</p>
							</td>
						</tr>

						<tr>
							<td>
								<a href="{{route('setup.roles')}}"><strong>Default Roles</strong></a>
								<br/>
								<p>
									Set Default Roles for Administrator, <br> Super Admin, Guest and
									Logged-in User
								</p>
							</td>
						</tr>
						<tr>
							<td>
								<a href="{{route('setup.redirects')}}"><strong>Roles Redirection</strong></a>
								<br/>
								<p>
									Set Redirect locations for roles so Users <br> with specific roles
									will be redirected to the specified location.
								</p>
							</td>
						</tr>


						</tbody>
					</table>
				</div>
			</div>

			<div class="col-sm-6">

				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Website Backup</h3>
					</div>
					<table class="table">
						<tbody>

						<tr>
							<td>
								<a href="{{route('setup.backups')}}"><strong>Click Here</strong></a>
							</td>
						</tr>

						</tbody>
					</table>
				</div>
			</div>

		</div>
	</div>
@endsection

