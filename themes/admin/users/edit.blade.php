<div class="card" id="section-edit" style="display: none">
	<div
		class="card-header bg-teal-400 header-elements-inline">
		<h6 class="card-title">Add User</h6>
	</div>

	<div class="card-body">
		<form method="post" action="#" name="form-edit" id="form-edit" class="form-horizontal">
			@csrf

			<div class="form-group row">

				<label for="first_name"
				       class="col-lg-2 col-form-label">First
					Name:</label>
				<div class="col-lg-4">
					<input
						type="text"
						id="first_name"
						name="first_name"
						class="form-control"
						placeholder="First Name"
					/>

				</div>

				<label for="last_name"
				       class="col-lg-2 col-form-label">Last
					Name:</label>
				<div class="col-lg-4">
					<input
						type="text"
						id="last_name"
						name="last_name"
						class="form-control"
						placeholder="Last Name"
					/>

				</div>
			</div>
			<div class="form-group row">

				<label for="email"
				       class="col-lg-2 col-form-label">Email:</label>
				<div class="col-lg-4">
					<input
						type="email"
						id="email"
						name="email"
						class="form-control"
						placeholder="Email Address"

					/>

				</div>

				<label for="password"
				       class="col-lg-2 col-form-label">Password:</label>
				<div class="col-lg-4">
					<input
						type="password"
						name="password"
						id="password"
						class="form-control"
						placeholder="password"
					/>

				</div>
			</div>

			<div class="form-group row">
				<label for="role_id"
				       class="col-lg-2 col-form-label">Role:</label>
				<div class="col-lg-4">
					<select name="role_id" id="roles" data-placeholder="Select Role"
					        class="form-control select2"></select>
				</div>

			</div>


			<div class="form-group row">
				<label for="phone"
				       class="col-lg-2 col-form-label">Phone
					Number:</label>
				<div class="col-lg-4">
					<input

						type="text"
						name="phone"
						data-mask="+1-999-999-9999"
						id="phone"
						class="form-control"
						placeholder="Phone Number"

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



