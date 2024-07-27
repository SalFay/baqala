<div class="card" id="section-overview">
	<div
		class="card-header bg-teal-400 header-elements-inline">
		<h6 class="card-title">All Payment Methods of {{$full_name}}</h6>
		<div class="header-elements">
			<div class="list-icons">
				<a href="#" data-url="{{route('payments.add')}}" class="btn btn-success btn-xs" data-action="add">
					Add Payment Method
				</a>
			</div>
		</div>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered table-striped table-hover" id="table-overview-jobs">
				<thead>
				<tr>
					<th>Source</th>
					<th>Name</th>
					<th>Account Title</th>
					<th>Account Branch</th>
					<th>Account #</th>
					<th>Action</th>
				</tr>
				</thead>
				<body>
				@foreach($payments as $method)

					<tr>
						<td>{{$method->source}}</td>
						<td>{{$method->name}}</td>
						<td>{{$method->account_title}}</td>
						<td>{{$method->account_branch}}</td>
						<td>{{$method->account_number}}</td>
						<td>
							<a href="#" data-url="{{route('payments.edit', $method->id)}}"
							   class="btn btn-primary btn-sm" data-action="edit"><i class="fas fa-edit"></i></a>
							<a href="#" data-url="{{route('payments.delete', $method->id)}}"
							   class="btn btn-danger btn-sm" data-action="delete"><i class="fas fa-trash"></i></a>

						</td>
					</tr>

				@endforeach
				</body>
			</table>

		</div>
	</div>
</div>
