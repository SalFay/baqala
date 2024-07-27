<div class="card" id="section-edit" style="display: none">
	<div
		class="card-header bg-teal-400 header-elements-inline">
		<h6 class="card-title">Add Vendor</h6>
	</div>

	<div class="card-body">
		<form method="post" action="#" name="form-edit" id="form-edit" class="form-horizontal">
			@csrf

			<div class="form-group row">

				<label for="first_name"
				       class="col-lg-2 col-form-label">
					Name:</label>
				<div class="col-lg-4">
					<input
						type="text"
						id="name"
						name="name"
						class="form-control"
						placeholder="Vendor Name"
					/>

				</div>

				<label for="phone"
				       class="col-lg-2 col-form-label">Mobile No:</label>
				<div class="col-lg-4">
					<input
						type="text"
						name="mobile"
						id="mobile"
						class="form-control"
						data-mask="+929999999999"
						placeholder="Mobile"
					/>

				</div>
			</div>


			<div class="form-group row">
				<label for="phone"
				       class="col-lg-2 col-form-label">Address:</label>
				<div class="col-lg-4">
					<input
						type="text"
						name="address"
						id="address"
						class="form-control"
						placeholder="Address"
					/>

				</div>


				<label class="col-lg-2 col-form-label"
				       for="status">Status:</label>
				<div class="col-lg-4">
					<select name="status" id="status" data-placeholder="Select Status" class="form-control"
					        data-module="select2">

						<option value="Active">Active</option>
						<option value="Suspended">Suspended</option>
					</select>
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



