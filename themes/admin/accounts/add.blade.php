<div class="card" id="section-add" style="display: none">
	<div
		class="card-header bg-teal-400 header-elements-inline">
		<h6 class="card-title">Add Payment</h6>
	</div>

	<div class="card-body">
		<form method="post" action="#" name="form-add" id="form-add" class="form-horizontal">
			@csrf
			<div class="form-group row">
				<div class="col-md-6">
					<label
						for="status">Type:</label>

					<select name="status" id="status" required data-placeholder="Select Type" class="form-control"
					        data-module="select2">
						<option value="">Select Type</option>
						<option value="Customer" selected>Customer</option>
						<option value="Vendor">Vendor</option>
					</select>

				</div>

				<div class="col-md-6" id="customerWrapper" style="display: none">
					<label>Customers:</label>
					<select name="customer_id" id="customers" data-placeholder="Select Customer"
					        class="form-control select2"></select>
				</div>
				<div class="col-md-6" id="vendorWrapper" style="display: none">
					<label>Vendors:</label>
					<select name="vendor_id" id="vendors" data-placeholder="Select Vendor"
					        class="form-control select2"></select>
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
					<label for="debit_add">Debit:</label>
					<input
						id="debit_add"
						type="text"
						name="debit"
						value="0"
						class="form-control"
					/>
				</div>

				<div class="col-md-6">
					<label
						for="credit_add">Credit:</label>
					<input
						id="credit_add"
						type="text"
						name="credit"
						value="0"
						class="form-control"
					/>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-6">
					<label for="debit_add">Discount:</label>
					<input
						id="discount"
						type="text"
						name="discount"
						value="0"
						class="form-control"
					/>
				</div>

				<label for="comments_add"
				       class="col-md-2 col-form-label">Comments:</label>
				<div class="col-md-4">
					<textarea id="comments_add" name="comments" cols="30" rows="5"></textarea>
				</div>

			</div>

		</form>
		<div class="box-footer text-right">
			<div class="btn-group">
				<input type="submit" id="form-submit-add" name="form-submit-add" class="btn btn-primary" form="form-add"
				       value="Add Category">
				<input type="reset" name="form-reset-add" id="form-reset-add" class="btn btn-default" form="form-add">
				<input type="button" name="form-cancel-add" id="form-cancel-add" class="btn btn-warning" value="Cancel">
			</div>
		</div>
	</div>

</div>

