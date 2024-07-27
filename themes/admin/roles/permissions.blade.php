<div class="card" id="section-permissions" style="display: none">
	<div
		class="card-header bg-teal-400 header-elements-inline">
		<h6 class="card-title">Permissions</h6>
		<div class="pull-right">
			<button type="submit" form="form-permissions" class="btn btn-xs btn-primary pull-right">Update</button>
		</div>
	</div>

	<div class="card-body">
		<form method="post" action="" id="form-permissions" name="form-permissions">
			@csrf
		</form>
		<div class="box-footer text-right">
			<button type="submit" form="form-permissions" class="btn btn-primary">Update</button>
			<button type="button" class="btn btn-warning" id="permissions-cancel">Cancel</button>
		</div>
	</div>
</div>