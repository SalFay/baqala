<div class="card" id="section-edit" style="display: none">
	<div
		class="card-header bg-teal-400 header-elements-inline">
		<h6 class="card-title">Add Payment</h6>
	</div>

	<div class="card-body">
		<form method="post" action="#" name="form-edit" id="form-edit" class="form-horizontal">
			@csrf
			<div class="form-group row">
				<div class="col-md-6">
					<label for="banks">Bank:</label>
					<select name="bank" required data-placeholder="Select Bank"
					        class="form-control select2 banks"></select>
				</div>

				<div class="col-md-6">
					<label
						for="cheque">Cheque #:</label>
					<input
						id="cheque"
						type="text"
						name="cheque"
						class="form-control"
					/>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-6">
					<label for="debit">Debit:</label>
					<input
						id="debit"
						type="text"
						name="debit"
						class="form-control"
					/>
				</div>

				<div class="col-md-6">
					<label
						for="credit">Credit:</label>
					<input
						id="credit"
						type="text"
						name="credit"
						class="form-control"
					/>
				</div>
			</div>
			<div class="form-group row">

				<label for="comments"
				       class="col-md-2 col-form-label">Comments:</label>
				<div class="col-md-4">
					<textarea id="comments" name="comments" cols="30" rows="5"></textarea>
				</div>

			</div>

		</form>
		<div class="box-footer text-right">
			<div class="btn-group">
				<input type="submit" id="form-submit" name="form-submit" class="btn btn-primary" form="form-edit"
				       value="Add Category">
				<input type="reset" name="form-reset" id="form-reset" class="btn btn-default" form="form-edit">
				<input type="button" name="form-cancel" id="form-cancel" class="btn btn-warning" value="Cancel">
			</div>
		</div>
	</div>

</div>

