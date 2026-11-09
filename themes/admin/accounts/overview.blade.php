@php
	$accRange = \App\Support\DateRange::parse( request( 'range' ), 'month' );
@endphp
<div class="card" id="section-overview">
	<div class="card-header header-elements-inline">
		<h6 class="card-title">All Debit / Credit</h6>
		<div class="header-elements">
			<div class="list-icons">
				<a href="#" data-url="{{route('accounts.expenses')}}" class="btn btn-primary btn-sm" data-action="add-expense">
					Add Expense
				</a>
				<a href="#" data-url="{{route('accounts.cash')}}" class="btn btn-light btn-sm" data-action="add-cash">
					Add Cash
				</a>
				<a href="#" data-url="{{route('accounts.transfer')}}" class="btn btn-light btn-sm" data-action="add-transfer">
					Bank Transfer
				</a>
				<a href="#" data-url="{{route('accounts.add')}}" class="btn btn-light btn-sm" data-action="add">
					Customer / Vendor Payment
				</a>
			</div>
		</div>
	</div>

	<div class="card-body rk">
		<div class="rk-filters" style="margin-bottom: .75rem">
			<div class="rk-field rk-field--range">
				<label for="acc-range">Date</label>
				<input type="text" id="acc-range" value="{{ $accRange->value() }}" class="form-control rk-range" autocomplete="off">
			</div>
			<div class="rk-field">
				<label for="acc-type">Type</label>
				<select id="acc-type" class="form-control">
					<option value="">All types</option>
					<option value="customer">Customers</option>
					<option value="vendor">Vendors</option>
					<option value="expense">Expenses</option>
					<option value="bank">Banks</option>
				</select>
			</div>
			<div class="rk-field">
				<label for="acc-source">Entries</label>
				<select id="acc-source" class="form-control">
					<option value="">All entries</option>
					<option value="manual">Added by hand (payments, expenses, cash, transfers)</option>
					<option value="sales">Posted by sales (Order No.)</option>
				</select>
			</div>
			<div class="rk-actions">
				<button type="button" id="filter-range" class="btn btn-primary">Apply</button>
				<button type="button" id="filter-refresh" class="btn btn-light">Reset</button>
			</div>
		</div>

		<div class="rk-kpis" style="--rk-tiles: 4">
			<div class="rk-kpi"><div class="rk-kpi__label">Entries</div><div class="rk-kpi__value" id="acc-entries">…</div></div>
			<div class="rk-kpi"><div class="rk-kpi__label">Debit</div><div class="rk-kpi__value" id="acc-debit">…</div></div>
			<div class="rk-kpi"><div class="rk-kpi__label">Credit</div><div class="rk-kpi__value" id="acc-credit">…</div></div>
			<div class="rk-kpi"><div class="rk-kpi__label">Discount</div><div class="rk-kpi__value" id="acc-discount">…</div></div>
		</div>

		<div class="table-responsive">
			<table class="table table-hover rk-table" id="table-account">
				<thead>
				<tr>
					<th>Date</th>
					<th>Type</th>
					<th>Name</th>
					<th class="num">Debit</th>
					<th class="num">Credit</th>
					<th class="num">Discount</th>
					<th>Cash / bank</th>
					<th>Comments</th>
					<th></th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<th colspan="3">Totals for the filter</th>
					<th class="num" id="acc-foot-debit"></th>
					<th class="num" id="acc-foot-credit"></th>
					<th class="num" id="acc-foot-discount"></th>
					<th colspan="3"></th>
				</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>

@include('admin.partials.report-kit')
