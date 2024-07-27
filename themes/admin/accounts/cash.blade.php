<div class="card" id="section-cash" style="display: none">
	<div
		class="card-header bg-teal-400 header-elements-inline">
		<h6 class="card-title">Add Cash</h6>
	</div>

	<div class="card-body">
		<form method="post" action="#" name="form-cash" id="form-cash" class="form-horizontal">
			@csrf

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
					<label for="debit_cash">Debit:</label>
					<input
						id="debit_cash"
						type="text"
						name="debit"
						class="form-control"
					/>
				</div>

				<div class="col-md-6">
					<label
						for="credit_cash">Credit:</label>
					<input
						id="credit_cash"
						type="text"
						name="credit"
						class="form-control"
					/>
				</div>
			</div>
			<div class="form-group row">

				<label for="comments_cash"
				       class="col-md-2 col-form-label">Comments:</label>
				<div class="col-md-4">
					<textarea id="comments_cash" name="comments" cols="30" rows="5"></textarea>
				</div>

			</div>

		</form>
		<div class="box-footer text-right">
			<div class="btn-group">
				<input type="submit" id="form-submit-cash" name="form-submit-cash" class="btn btn-primary" form="form-cash"
				       value="Add Category">
				<input type="reset" name="form-reset-cash" id="form-reset-cash" class="btn btn-default" form="form-cash">
				<input type="button" name="form-cancel-cash" id="form-cancel-cash" class="btn btn-warning" value="Cancel">
			</div>
		</div>
	</div>

</div>

