<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>@yield('page-title') - {{config('app.name')}}</title>

	<link href="{{asset('assets/css/app.css')}}" type="text/css" rel="stylesheet">

	@stack('head')
	<style>

     .page-header {
         display: none;
     }

     .col-form-label {
         font-weight: bold;
     }

     table.dataTable thead .sorting:after {
         font-family: 'icomoon' !important;
         opacity: 0.2;
         content: "" !important;
     }

     table.dataTable thead .sorting_desc:after {
         content: "" !important;
         font-family: 'icomoon' !important;
     }

     table.dataTable thead .sorting_asc:after {
         content: "" !important;
         font-family: 'icomoon' !important;
     }

     table.dataTable {
         border: 1px solid #ddd !important;
     }

     table.dataTable td, table.dataTable th {
         white-space: nowrap;
         text-align: center;
         font-size: .8125rem;
     }

     table td, table th {
         white-space: nowrap;
         text-align: center;
         padding: 0.25rem 0.5em !important;
         font-size: .8125rem;
         max-width: 100%;
     }


     .help-block {
         color: red;
     }

     fieldset {
         border: 1px solid #ddd !important;
         margin-bottom: 10px;
         padding: 10px;
         position: relative;
         border-radius: 4px;
     }

     legend {
         font-size: 14px;
         font-weight: bold;
         font-family: Georgia;
         width: 35%;
         border: 1px solid #ddd;
         border-radius: 4px;
         padding: 4px 4px 4px 9px;
         padding: 5px 5px 5px 10px;
     }


	</style>
</head>

<body>

<!-- Main navbar -->
<div class="navbar navbar-expand-md navbar-dark">
	<div class="navbar-brand">
		<a href="#" class="d-inline-block">
			<h4 style="color: #ffffff;font-weight: 700;font-size: 16px;" class="mr-2"> {{option('title')}} </h4>

		</a>
	</div>

	<div class="d-md-none">
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-mobile">
			<i class="icon-tree5"></i>
		</button>
		<button class="navbar-toggler sidebar-mobile-main-toggle" type="button">
			<i class="icon-paragraph-justify3"></i>
		</button>
	</div>

	<div class="collapse navbar-collapse" id="navbar-mobile">
		<ul class="navbar-nav">
			<li class="nav-item">
				<a href="#" class="navbar-nav-link sidebar-control sidebar-main-toggle d-none d-md-block">
					<i class="icon-paragraph-justify3"></i>
				</a>
			</li>


		</ul>


		<span class="ml-md-3 mr-md-auto">&nbsp;</span>

		<ul class="navbar-nav">

			<li class="nav-item dropdown dropdown-user">
				<a href="#" class="navbar-nav-link d-flex align-items-center dropdown-toggle" data-toggle="dropdown">
					<img src="#" class="rounded-circle mr-2"
					     height="34" alt="">
					<span>{{Auth::user()->full_name}}</span>

				</a>

				<div class="dropdown-menu dropdown-menu-right">
					<a href="#" class="dropdown-item"><i class="icon-user-plus"></i> My profile</a>
					<div class="dropdown-divider"></div>
					<form method="POST" action="{{route('logout')}}">
						@csrf
						<a href="{{route('logout')}}" onclick="event.preventDefault();
                                                            this.closest('form').submit();" class="dropdown-item"><i
								class="icon-switch2"></i> Logout</a>
					</form>

				</div>
			</li>
		</ul>
	</div>
</div>
<!-- /main navbar -->


<!-- Page content -->
<div class="page-content">

	<!-- Main sidebar -->
	<div class="sidebar sidebar-dark sidebar-main sidebar-expand-md hidden-print">

		<!-- Sidebar mobile toggler -->
		<div class="sidebar-mobile-toggler text-center">
			<a href="#" class="sidebar-mobile-main-toggle">
				<i class="icon-arrow-left8"></i>
			</a>
			Navigation
			<a href="#" class="sidebar-mobile-expand">
				<i class="icon-screen-full"></i>
				<i class="icon-screen-normal"></i>
			</a>
		</div>
		<!-- /sidebar mobile toggler -->


		<!-- Sidebar content -->
		<div class="sidebar-content">

			<!-- User menu -->
			<div class="sidebar-user">
				<div class="card-body">
					<div class="media">
						<div class="mr-3"><a href="#" class="btn bg-blue rounded-round btn-icon btn-sm"><span
									class="letter-icon">{{Auth::user()->first_letter}}</span></a></div>


						<div class="media-body">
							<div class="media-title font-weight-semibold">{{Auth::user()->full_name}}</div>
							<div class="font-size-xs opacity-50">
								<i class="icon-pin font-size-sm"></i> &nbsp;Mingora
							</div>
						</div>

					</div>
				</div>
			</div>
			<!-- /user menu -->


			<!-- Main navigation -->
		@include('admin.layouts.menu')
		<!-- /main navigation -->

		</div>
		<!-- /sidebar content -->

	</div>
	<!-- /main sidebar -->


	<!-- Main content -->
	<div class="content-wrapper">

		<!-- Page header -->
		<div class="page-header page-header-light hidden-print">
			<div class="page-header-content header-elements-md-inline">
				<div class="page-title d-flex">
					<h4>
						<i class="icon-arrow-left52 mr-2"></i>
						@yield('heading')

					</h4>

					<a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
				</div>
				<br>
				<p>@yield('sub-heading')</p>
				<div class="header-elements d-none">
					<div class="d-flex justify-content-center">
						@yield('heading-buttons')
					</div>
				</div>
			</div>

			<div
				class="breadcrumb-line breadcrumb-line-light alpha-success border-top-success header-elements-md-inline">
				<div class="d-flex">
					<div class="breadcrumb">
						<a href="{{url('/admin')}}" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
						<span class="breadcrumb-item active"> @yield('breadcrumbs')</span>

					</div>

					<a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
				</div>

				<div class="header-elements d-none">
					<div class="breadcrumb justify-content-center">
						@yield('breadcrumb-buttons')
					</div>
				</div>
			</div>
		</div>
		<!-- /page header -->


		<!-- Content area -->
		<div class="content">
			@impersonating
			<div class="alert alert-primary alert-styled-left">
				You are logged-in as {!! user()->full_name !!}. <a href="{{ route('impersonate.stop') }}">
					Stop Session</a>
			</div>

			@endImpersonating
			@yield('content')
		</div>
		<!-- /content area -->


		<!-- Footer -->
		<div class="navbar navbar-expand-lg navbar-light">
			<div class="text-center d-lg-none w-100">
				<button type="button" class="navbar-toggler dropdown-toggle" data-toggle="collapse"
				        data-target="#navbar-footer">
					<i class="icon-unfold mr-2"></i>
					Footer
				</button>
			</div>

			<div class="navbar-collapse collapse" id="navbar-footer">
					<span class="navbar-text">
						&copy; 2019 - {{date('Y')}}. <a href="http://xpertzdev.com/">Xpertz Dev IT Solution</a>
					</span>

				<ul class="navbar-nav ml-lg-auto">

				</ul>
			</div>
		</div>
		<!-- /footer -->

	</div>
	<!-- /main content -->

</div>
<!-- /page content -->
<script src="{{asset('assets/js/app.js')}}"></script>
<script src="{{asset('assets/keyMaster.js')}}"></script>
<script src="{{asset('assets/input-mask.js')}}"></script>
<script src="{{asset('assets/scanner.js')}}"></script>
<script src="{{asset('themes/default/apex/apexcharts.min.js')}}"></script>
<script>
	$(document).ready(function () {
		ui.init()
		$('[data-popup="tooltip"]').tooltip()
	})

	let delay = (function () {
		let timer = 0
		return function (callback, ms) {
			clearTimeout(timer)
			timer = setTimeout(callback, ms)
		}
	})()
</script>
@stack('footer')
</body>
</html>
