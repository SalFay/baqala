@extends('layouts.app')
@section('page-title', 'Profile')
@section('breadcrumbs')
	<li class="breadcrumb-item active">Profile</li>
@endsection
@section('content')

	<div class="container-fluid">
		<div class="row">

			<div class="col-md-6">

				<div class="card card-primary">
					<div class="card-header">
						<h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
							{{ __('Profile Information') }}
						</h2>

						<p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
							{{ __("Update your account's profile information and email address.") }}
						</p>
					</div>


					<form id="send-verification" method="post" action="{{ route('verification.send') }}">
						@csrf
					</form>

					<form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
						@csrf
						@method('patch')
						<div class="card-body">
							@include('profile.partials.update-profile-information-form')
						</div>

						<div class="card-footer">
							<button type="submit" class="btn btn-primary">{{ __('Save') }}</button>

							@if (session('status') === 'profile-updated')
								<p
									x-data="{ show: true }"
									x-show="show"
									x-transition
									x-init="setTimeout(() => show = false, 2000)"
									class="text-sm text-gray-600 dark:text-gray-400"
								>{{ __('Saved.') }}</p>
							@endif       </div>
					</form>
				</div>


			</div>
			<div class="col-md-6">

				<div class="card card-primary">
					<div class="card-header">
						<h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
							{{ __('Update Password') }}
						</h2>

						<p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
							{{ __('Ensure your account is using a long, random password to stay secure.') }}
						</p>
					</div>


					<form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
						@csrf
						@method('put')

						<div class="card-body">
							@include('profile.partials.update-password-form')
						</div>

						<div class="card-footer">
							<button type="submit" class="btn btn-primary">{{ __('Save') }}</button>

							@if (session('status') === 'password-updated')
								<p
									x-data="{ show: true }"
									x-show="show"
									x-transition
									x-init="setTimeout(() => show = false, 2000)"
									class="text-sm text-gray-600 dark:text-gray-400"
								>{{ __('Saved.') }}</p>
							@endif       </div>
					</form>
				</div>


			</div>


		</div>

	</div>


@endsection
