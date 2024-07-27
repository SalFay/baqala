<form method="post" id="filter-form">
	<div class="row">
		<div class="col-md-4 col-12 mb-2 position-relative">
			<label class="form-label" for="category_id">Category</label>
			<select name="category_id" id="categoryFilter" data-placeholder="Select Category"
			        class="form-control select2"></select>
		</div>
		<div class="col-md-4 col-12 mb-2 position-relative">
			<div style="float: right; margin-top: 21px;">
				<input type="submit" value="Filter" class="btn btn-primary ">
				<input type="reset" id="reset-filters" class="btn btn-warning">
			</div>
		</div>
	</div>
</form>