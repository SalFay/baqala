<form method="post" id="filter-form">
	<div class="row">


		<div class="col-md-3 col-12 mb-2 position-relative">
			<label class="form-label" for="role_id">SMS/Email/Query Type</label>
			<select class="form-control select2 " id="type" name="type">
				<option value="">Select Type</option>
				<option value="reg">Registration SMS / Email</option>
				<option value="project">Project Based SMS / Email</option>
				<option value="manual">Manual SMS / Email</option>
				<option value="query">Query</option>
				<option value="job">Job Applications</option>
			</select>
		</div>


		<div class="col-md-3 col-12 mb-2 position-relative">
			<div style="float: right; margin-top: 21px;">
				<input type="submit" value="Filter" class="btn btn-primary float-right">
				<input type="reset" id="reset-filters" class="btn btn-warning float-right">
			</div>
		</div>
	</div>
</form>
