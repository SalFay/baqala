<div class="card" id="section-transfer" style="display: none">
	<div
		class="card-header bg-teal-400 header-elements-inline">
		<h6 class="card-title">Transfer from Bank to Bank</h6>
	</div>

	<div class="card-body">
		<form method="post" action="#" name="form-transfer" id="form-transfer" class="form-horizontal">
			@csrf

			<div class="form-group row">
				<div class="col-md-6">
					<label for="banks">From Bank:</label>
					<select name="from_bank" data-placeholder="Select Bank"
					        class="form-control select2 banks"></select>
				</div>
				<div class="col-md-6">
					<label for="banks">To Bank:</label>
					<select name="to_bank" data-placeholder="Select Bank"
					        class="form-control select2 banks"></select>
				</div>
			</div>

			<div class="form-group row">

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

				<label for="comments_cash"
				       class="col-md-2 col-form-label">Comments:</label>
				<div class="col-md-4">
					<textarea id="comments_cash" name="comments" cols="30" rows="5"></textarea>
				</div>

			</div>

		</form>
		<div class="box-footer text-right">
			<div class="btn-group">
				<input type="submit" id="form-submit-transfer" name="form-submit-transfer" class="btn btn-primary"
				       form="form-transfer"
				       value="Add Category">
				<input type="reset" name="form-reset-transfer" id="form-reset-transfer" class="btn btn-default"
				       form="form-transfer">
				<input type="button" name="form-cancel-transfer" id="form-cancel-transfer" class="btn btn-warning"
				       value="Cancel">
			</div>
		</div>
	</div>

</div>

