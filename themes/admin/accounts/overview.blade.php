<div class="card" id="section-overview">
	<div
		class="card-header bg-brown-300 header-elements-inline">
		<h6 class="card-title">All Debit / Credit</h6>
		<div class="header-elements">
			<div class="list-icons">
				<a href="#" data-url="{{route('accounts.expenses')}}" class="btn btn-primary btn-xs" data-action="add-expense">
					Add Expenses
				</a>
				<a href="#" data-url="{{route('accounts.cash')}}" class="btn btn-info btn-xs" data-action="add-cash">
					Add Cash
				</a>
				<a href="#" data-url="{{route('accounts.transfer')}}" class="btn btn-warning btn-xs" data-action="add-transfer">
					Bank Transfer
				</a>
				<a href="#" data-url="{{route('accounts.add')}}" class="btn btn-success btn-xs" data-action="add">
					Add Customer / Vendor Payment
				</a>
			</div>
		</div>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			@include('admin.dateRange')
			<table style="width: fit-content" class="table table-bordered table-striped table-hover" id="table-account">
				<thead>
				<tr>
					<th>Date</th>
					<th>Type</th>
					<th>Name</th>
					<th>Debit</th>
					<th>Credit</th>
					<th>Discount</th>
					<th>Bank</th>
					<th>Comments</th>
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
					<th></th>
					<th></th>
				</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>
