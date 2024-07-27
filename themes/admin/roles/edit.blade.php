<div class="card" id="section-edit" style="display: none">
	<div
		class="card-header bg-teal-400 header-elements-inline">
		<h6 class="card-title">Add User</h6>
	</div>

	<div class="card-body">
		<form method="post" action="#" name="form-edit" id="form-edit" class="form-horizontal">
			@csrf
			<div class="form-group">
				<label for="name">Name:</label>
				<input
					id="name"
					type="text"
					name="name"
					placeholder="Name"
					class="form-control"
				/>
			</div>

			<div class="form-group">
				<label
					for="description">Description:</label>
				<textarea
					id="description"
					name="description"
					placeholder="Role Description (Optional)"
					class="form-control"
				></textarea>
			</div>


		</form>
		<div class="box-footer text-right">
			<div class="btn-group">
				<input type="submit" id="form-submit" name="form-submit" class="btn btn-primary" form="form-edit"
				       value="Add Role">
				<input type="reset" name="form-reset" id="form-reset" class="btn btn-default" form="form-edit">
				<input type="button" name="form-cancel" id="form-cancel" class="btn btn-warning" value="Cancel">
			</div>
		</div>
	</div>

</div>

