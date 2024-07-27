<div class="card" id="section-overview">
	<div
		class="card-header bg-teal-400 header-elements-inline">
		<h6 class="card-title">All Customers</h6>

		<div class="header-elements">
			<div class="list-icons">
				<a href="#" data-url="{{route('customers.add')}}" class="btn btn-success btn-xs" data-action="add">
					Add Customer
				</a>
			</div>
		</div>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered table-striped table-hover" id="table-customer">
				<thead>
				<tr>
					<th> First Name</th>
					<th> Last Name</th>
					<th>Mobile</th>
					<th>Address</th>
					<th>Credit / Debit</th>
					<th>Status</th>
					<th>Actions</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
				</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>

