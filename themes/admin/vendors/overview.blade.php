<div class="card" id="section-overview">
	<div
		class="card-header bg-teal-400 header-elements-inline">
		<h6 class="card-title">All Vendors</h6>

		<div class="header-elements">
			<div class="list-icons">
				<a href="#" data-url="{{route('vendors.add')}}" class="btn btn-success btn-xs" data-action="add">
					Add Vendor
				</a>
			</div>
		</div>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered table-striped table-hover" id="table-vendors">
				<thead>
				<tr>
					<th> Name</th>
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

				</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>

