@extends('admin.layouts.admin')

@section('heading')
	<span class="font-weight-semibold">Home</span> -
	Dashboard
@endsection

@section('heading-buttons')
	<a href="#" class="btn btn-link btn-float text-default"><i
			class="icon-bars-alt text-primary"></i><span>Statistics</span></a>
	<a href="#" class="btn btn-link btn-float text-default"><i
			class="icon-calculator text-primary"></i> <span>Invoices</span></a>
	<a href="#" class="btn btn-link btn-float text-default"><i
			class="icon-calendar5 text-primary"></i> <span>Schedule</span></a>
@endsection

@section('breadcrumbs')
	<span class="breadcrumb-item active">Dashboard</span>
@endsection

@section('breadcrumb-buttons')
	<a href="#" class="breadcrumb-elements-item">
		<i class="icon-comment-discussion mr-2"></i>
		Support
	</a>
	<div class="breadcrumb-elements-item dropdown p-0">
		<a href="#" class="breadcrumb-elements-item dropdown-toggle" data-toggle="dropdown">
			<i class="icon-gear mr-2"></i>
			Settings
		</a>
		<div class="dropdown-menu dropdown-menu-right">
			<a href="#" class="dropdown-item"><i class="icon-user-lock"></i> Account security</a>
			<a href="#" class="dropdown-item"><i class="icon-statistics"></i> Analytics</a>
			<a href="#" class="dropdown-item"><i class="icon-accessibility"></i> Accessibility</a>
			<div class="dropdown-divider"></div>
			<a href="#" class="dropdown-item"><i class="icon-gear"></i> All settings</a>
		</div>
	</div>
@endsection

@section('content')
	Your Content Here
@endsection


@push('head')
	{{--Any Style or head tag data--}}
@endpush

@push('footer')
	{{--Any Javascript or before Body data--}}
@endpush
