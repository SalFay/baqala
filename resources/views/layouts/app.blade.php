<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>{{ config('app.name', 'Laravel') }}</title>
	<link href="{{ asset('css/tailwind.css') }}" rel="stylesheet">
	<link href="{{ asset('css/app.css') }}" rel="stylesheet">
	<link href="{{ asset('css/admin-lte/adminlte.css') }}" rel="stylesheet">

	@livewireStyles
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
	<!-- Main Header -->
	<nav class="main-header navbar navbar-expand navbar-white navbar-light">
		<!-- Left navbar links -->
		<ul class="navbar-nav">
			<li class="nav-item">
				<a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
						class="fas fa-bars"></i></a>
			</li>
		</ul>

		<ul class="navbar-nav ml-auto">
			<li class="nav-item dropdown user-menu">
				<a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
					<img src="https://assets.infyom.com/logo/blue_logo_150x150.png"
					     class="user-image img-circle elevation-2" alt="User Image">
					<span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
				</a>
				<ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
					<!-- User image -->
					<li class="user-header bg-primary">
						<img src="https://assets.infyom.com/logo/blue_logo_150x150.png"
						     class="img-circle elevation-2" alt="User Image">
						<p>
							{{ Auth::user()->name }}
							<small>Member since {{ Auth::user()->created_at->format('M. Y') }}</small>
						</p>
					</li>
					<!-- Menu Footer-->
					<li class="user-footer">
						<a href="{{route('profile.edit')}}" class="btn btn-default btn-flat">Profile</a>
						<a href="{{route('logout')}}" class="btn btn-default btn-flat float-right"
						   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
							Sign out
						</a>
						<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
							@csrf
						</form>
					</li>
				</ul>
			</li>
		</ul>
	</nav>

	<!-- Left side column. contains the logo and sidebar -->
@include('layouts.sidebar')

<!-- Content Wrapper. Contains page content -->
	<div class="content-wrapper">
		<section class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1>    @yield('page-title')</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
							@yield('breadcrumbs')
						</ol>
					</div>
				</div>
			</div>
		</section>
		<section class="content">
			@yield('content')
		</section>
	</div>

	<!-- Main Footer -->
	<footer class="main-footer">
		<div class="float-right d-none d-sm-block">
			<b>Version</b> 3.1.0
		</div>
		<strong>Copyright &copy; 2014-2023 <a href="https://adminlte.io">AdminLTE.io</a>.</strong> All rights
		reserved.
	</footer>
</div>


<script src="{{ asset('js/app.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.7.0.slim.min.js" integrity="sha256-tG5mcZUtJsZvyKAxYLVXrmjKBVLd6VpVccqz/r4ypFE=" crossorigin="anonymous"></script>
<script src="{{ asset('js/admin-lte/adminlte.js') }}"></script>
@livewireScripts

</body>
</html>

