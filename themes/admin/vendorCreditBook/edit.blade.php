<div class="card" id="section-edit" style="display: none">
	<div
		class="card-header bg-teal-400 header-elements-inline">
		<h6 class="card-title">Add Payment</h6>
	</div>

	<div class="card-body">
		<form method="post" action="#" name="form-edit" id="form-edit" class="form-horizontal">
			@csrf

			<div class="form-group row" id="vendorWrapper">
				<label for="vendor"
				       class="col-lg-2 col-form-label">Vendor Name:</label>
				<div class="col-lg-4">
					<select name="vendor_id" id="vendor" data-placeholder="Select Customer"
					        class="form-control select2"></select>
				</div>


			</div>
			<div class="form-group row">
				<label for="comments"
				       class="col-lg-2 col-form-label">Comments:</label>
				<div class="col-lg-4">
                    <textarea
	                    name="comments"
	                    id="comments"
	                    class="form-control"
	                    cols="5"
	                    rows="5"
                    ></textarea>

				</div>
			</div>

			<div class="form-group row">
				<label for="credit"
				       class="col-lg-2 col-form-label">Credit Amount:</label>
				<div class="col-lg-4">
					<input
						type="number"
						name="credit"
						id="credit"
						class="form-control"
						placeholder="Enter Credit Amount"
					/>

				</div>
				<label for="debit"
				       class="col-lg-2 col-form-label">Debit Amount:</label>
				<div class="col-lg-4">
					<input
						type="number"
						name="debit"
						id="debit"
						class="form-control"
						placeholder="Enter Debit Amount"
					/>
				</div>
			</div>


		</form>
		<div class="box-footer text-right">
			<div class="btn-group">
				<input type="submit" id="form-submit" name="form-submit" class="btn btn-primary" form="form-edit"
				       value="Add User">
				<input type="reset" name="form-reset" id="form-reset" class="btn btn-default" form="form-edit">
				<input type="button" name="form-cancel" id="form-cancel" class="btn btn-warning" value="Cancel">
			</div>
		</div>
	</div>

</div>



