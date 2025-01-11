<div class="card" id="section-edit" style="display: none">
	<div
		class="card-header bg-teal-400 header-elements-inline">
		<h6 class="card-title">Add Product</h6>
	</div>

	<div class="card-body">
		<form method="post" onsubmit="return false;" action="#" name="form-edit" id="form-edit" class="form-horizontal">
			@csrf

			<div class="form-group row">
				<label for="pid"
				       class="col-lg-2 col-form-label">
					Product Barcode:</label>
				<div class="col-lg-4">
					<input type="text" name="pid" id="barcode" autocomplete="off" autofocus class="form-control"/>

				</div>
				<label for="last_name"
				       class="col-lg-2 col-form-label">Category:</label>
				<div class="col-lg-4">
					<select name="category_id" id="categories" data-placeholder="Select Category"
					        class="form-control select2"></select>
				</div>
			</div>
			<div class="form-group row">

				<label for="first_name"
				       class="col-lg-2 col-form-label">
					Urdu Name:</label>
				<div class="col-lg-4">
					<input
						type="text"
						id="arabic_name"
						name="arabic_name"
						class="form-control"
						placeholder="Product Arabic Name"
					/>

				</div>

				<label for="first_name"
				       class="col-lg-2 col-form-label">
					Name:</label>
				<div class="col-lg-4">
					<input
						type="text"
						id="name"
						name="name"
						class="form-control"
						placeholder="Product Name"
					/>

				</div>


			</div>


			<div class="form-group row">
				<label for="phone"
				       class="col-lg-2 col-form-label">Purchase Price:</label>
				<div class="col-lg-4">
					<input
						type="text"
						name="purchase_price"
						id="purchase_price"
						class="form-control"
						placeholder="e.g. 319.98"
					/>

				</div>
				<label for="phone"
				       class="col-lg-2 col-form-label">Sale Price:</label>
				<div class="col-lg-4">
					<input
						type="text"
						name="sale_price"
						id="sale_price"
						class="form-control"
						placeholder="e.g. 400.98"
					/>

				</div>
			</div>
			<div class="form-group row">
				<label class="col-lg-2 col-form-label"
				       for="status">Status:</label>
				<div class="col-lg-4">
					<select name="status" id="status" data-placeholder="Select Status" class="form-control"
					        data-module="select2">

						<option value="Active">Active</option>
						<option value="Suspended">Suspended</option>
					</select>
				</div>
				<label for="phone"
				       class="col-lg-2 col-form-label">Taxable:</label>
				<div class="col-lg-4">
					<div class="form-check">
						<input type="checkbox" id="taxable" class="form-check-input" name="taxable"/>
					</div>
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



