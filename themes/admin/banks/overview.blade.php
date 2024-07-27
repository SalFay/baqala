<div class="card" id="section-overview">
	<div
		class="card-header bg-teal-400 header-elements-inline">
		<h6 class="card-title">All Banks</h6>

		<div class="header-elements">
			<div class="list-icons">
				<a href="#" data-url="{{route('bank.add')}}" class="btn btn-success btn-xs" data-action="add">
					Add Bank
				</a>
			</div>
		</div>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered table-striped table-hover" id="table-bank">
				<thead>
				<tr>
					<th>Name</th>
					<th>Account Number</th>
					<th>Actions</th>
				</tr>
				</thead>

			</table>
		</div>
	</div>
</div>
