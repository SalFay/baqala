<div class="card" id="section-edit" style="display: none">
	<div
		class="card-header bg-teal-400 header-elements-inline">
		<h6 class="card-title">Add Customer</h6>
	</div>

	<div class="card-body">
		<form method="post" action="#" name="form-edit" id="form-edit" class="form-horizontal">
			@csrf
			<fieldset>
				<legend>Customer Info</legend>
				<div class="form-group row">

					<label for="first_name"
					       class="col-lg-2 col-form-label">
						First Name:</label>
					<div class="col-lg-4">
						<input
							type="text"
							id="first_name"
							name="first_name"
							class="form-control"
							placeholder="Customer First Name"
						/>

					</div>

					<label for="last_name"
					       class="col-lg-2 col-form-label">
						Last Name:</label>
					<div class="col-lg-4">
						<input
							type="text"
							id="last_name"
							name="last_name"
							class="form-control"
							placeholder="Customer Last Name"
						/>

					</div>

				</div>
				<div class="form-group row">

					<label for="business_name"
					       class="col-lg-2 col-form-label">Business Name:</label>
					<div class="col-lg-4">
						<input
							type="text"
							name="business_name"
							id="business_name"
							class="form-control"
							placeholder="Business Name"
						/>

					</div>

					<label for="address"
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

				</div>
			</fieldset>
			<fieldset>
				<legend>Billing Info</legend>
				<div class="form-group row">
					<label for="billing_address"
					       class="col-lg-2 col-form-label">Billing Address:</label>
					<div class="col-lg-4">
						<input
							type="text"
							name="billing_address"
							id="billing_address"
							class="form-control"
							placeholder="Billing Address"
						/>

					</div>


					<label for="billing_city"
					       class="col-lg-2 col-form-label">Billing City:</label>
					<div class="col-lg-4">
						<input
							type="text"
							name="billing_city"
							id="billing_city"
							class="form-control"
							placeholder="Billing City"
						/>

					</div>
				</div>
				<div class="form-group row">

					<label for="billing_state"
					       class="col-lg-2 col-form-label">Billing State:</label>
					<div class="col-lg-4">
						<input
							type="text"
							name="billing_state"
							id="billing_state"
							class="form-control"
							placeholder="Billing State"
						/>

					</div>

					<label for="billing_zipcode"
					       class="col-lg-2 col-form-label">Billing ZipCode:</label>
					<div class="col-lg-4">
						<input
							type="text"
							name="billing_zipcode"
							id="billing_zipcode"
							class="form-control"
							placeholder="Billing ZipCode"
						/>

					</div>

				</div>
				<div class="form-group row">

					<label for="billing_country"
					       class="col-lg-2 col-form-label">Billing Country:</label>
					<div class="col-lg-4">
						<input
							type="text"
							name="billing_country"
							id="billing_country"
							class="form-control"
							placeholder="Billing Country"
						/>

					</div>
				</div>
			</fieldset>
			<fieldset>
				<legend>Shipping Info <input
						type="checkbox"
						id="ship_address"
						class="form-check-input"
						onclick="shipInfo()"
					/></legend>
				<div class="form-group row">

					<label for="shipping_address"
					       class="col-lg-2 col-form-label">Shipping Address:</label>
					<div class="col-lg-4">
						<input
							type="text"
							name="shipping_address"
							id="shipping_address"
							class="form-control"
							placeholder="Shipping Address"
						/>

					</div>

					<label for="shipping_city"
					       class="col-lg-2 col-form-label">Shipping City:</label>
					<div class="col-lg-4">
						<input
							type="text"
							name="shipping_city"
							id="shipping_city"
							class="form-control"
							placeholder="Shipping City"
						/>

					</div>
				</div>
				<div class="form-group row">

					<label for="shipping_state"
					       class="col-lg-2 col-form-label">Shipping State:</label>
					<div class="col-lg-4">
						<input
							type="text"
							name="shipping_state"
							id="shipping_state"
							class="form-control"
							placeholder="Shipping State"
						/>

					</div>

					<label for="shipping_zipcode"
					       class="col-lg-2 col-form-label">Shipping ZipCode:</label>
					<div class="col-lg-4">
						<input
							type="text"
							name="shipping_zipcode"
							id="shipping_zipcode"
							class="form-control"
							placeholder="Shipping ZipCode"
						/>

					</div>
				</div>

				<div class="form-group row">

					<label for="shipping_country"
					       class="col-lg-2 col-form-label">Shipping Country:</label>
					<div class="col-lg-4">
						<input
							type="text"
							name="shipping_country"
							id="shipping_country"
							class="form-control"
							placeholder="Shipping Country"
						/>
					</div>
				</div>
			</fieldset>
			<fieldset>
				<legend>Other Info</legend>
				<div class="form-group row">
					<label for="phone_home"
					       class="col-lg-2 col-form-label">Home Phone No:</label>
					<div class="col-lg-4">
						<input
							type="text"
							name="phone_home"
							id="phone_home"
							class="form-control"
							placeholder="Home Phone No"
						/>

					</div>

					<label for="phone_work"
					       class="col-lg-2 col-form-label">Work Phone No:</label>
					<div class="col-lg-4">
						<input
							type="text"
							name="phone_work"
							id="phone_work"
							class="form-control"
							placeholder="Work Phone No"
						/>

					</div>
				</div>
				<div class="form-group row">
					<label for="phone_mobile"
					       class="col-lg-2 col-form-label">Mobile No:</label>
					<div class="col-lg-4">
						<input
							type="text"
							name="phone_mobile"
							id="phone_mobile"
							class="form-control"
							placeholder="Mobile No"
						/>

					</div>

					<label for="phone_other"
					       class="col-lg-2 col-form-label">Other Mobile No:</label>
					<div class="col-lg-4">
						<input
							type="text"
							name="phone_other"
							id="phone_other"
							class="form-control"
							placeholder="Other Mobile No"
						/>

					</div>
				</div>
				<div class="form-group row">
					<label for="email"
					       class="col-lg-2 col-form-label">Email Address:</label>
					<div class="col-lg-4">
						<input
							type="text"
							name="email"
							id="email"
							class="form-control"
							placeholder="Email Address"
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
			</fieldset>
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



