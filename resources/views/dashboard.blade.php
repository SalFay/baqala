@extends('layouts.app')
@section('page-title', 'Dashboard')
@section('content')


<div class="card card-info">
	<div class="card-header">
		<h3 class="card-title">Default Card Example</h3>
		<div class="card-tools">
			<!-- Buttons, labels, and many other things can be placed here! -->
			<!-- Here is a label for example -->
			<span class="badge badge-primary">Add New User</span>
		</div>
		<!-- /.card-tools -->
	</div>
	<!-- /.card-header -->
	<div class="card-body">
		<livewire:user-table/>
	</div>
	<!-- /.card-body -->

</div>
<!-- /.card -->
@endsection
