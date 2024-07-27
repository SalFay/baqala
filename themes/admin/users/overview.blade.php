<div class="card" id="section-overview">
	<div
		class="card-header bg-teal-400 header-elements-inline">
		<h6 class="card-title">All Users</h6>

		<div class="header-elements">
			<div class="list-icons">
				<a href="#" data-url="{{route('users.add')}}" class="btn btn-success btn-xs" data-action="add">
					Add User
				</a>
			</div>
		</div>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered table-striped table-hover" id="table-users">
				<thead>
				<tr>
					<th>First Name</th>
					<th>Last Name</th>
					<th>E-mail</th>
					<th>Roles</th>
					<th>Verified</th>
					<th>Status</th>
					<th>Actions</th>
				</tr>
				</thead>
			</table>
		</div>
	</div>
</div>

