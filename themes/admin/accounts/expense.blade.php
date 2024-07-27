<div class="card" id="section-expense" style="display: none">
	<div
		class="card-header bg-teal-400 header-elements-inline">
		<h6 class="card-title">Add Expense</h6>
	</div>

	<div class="card-body">
		<form method="post" action="#" name="form-expense" id="form-expense" class="form-horizontal">
			@csrf
			<div class="form-group row">
				<div class="col-md-6">
					<label for="expenses">Expense Type:</label>
					<select name="expense" data-placeholder="Select Expense Type"
					        class="form-control select2 expenses"></select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-6">
					<label for="banks">Bank:</label>
					<select name="bank" data-placeholder="Select Bank"
					        class="form-control select2 banks"></select>
				</div>

				<div class="col-md-6">
					<label
						for="cheque">Cheque #:</label>
					<input
						type="text"
						name="cheque"
						value="0"
						class="form-control"
					/>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-6">
					<label for="debit_expense">Amount:</label>
					<input
						id="debit_expense"
						type="text"
						name="debit"
						value="0"
						class="form-control"
					/>
				</div>

				<label for="comments_expense"
				       class="col-md-2 col-form-label">Comments:</label>
				<div class="col-md-4">
					<textarea id="comments_expense" name="comments" cols="30" rows="5"></textarea>
				</div>

			</div>

		</form>
		<div class="box-footer text-right">
			<div class="btn-group">
				<input type="submit" id="form-submit-expense" name="form-submit-expense" class="btn btn-primary" form="form-expense"
				       value="Add Category">
				<input type="reset" name="form-reset-expense" id="form-reset-expense" class="btn btn-default" form="form-expense">
				<input type="button" name="form-cancel-expense" id="form-cancel-expense" class="btn btn-warning" value="Cancel">
			</div>
		</div>
	</div>

</div>

