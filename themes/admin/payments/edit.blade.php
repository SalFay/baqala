<div class="card" id="section-edit" style="display: none">
	<div class="card-header bg-teal-400 header-elements-inline">
		<h6 class="card-title">Add Payments</h6>
	</div>
	<div class="card-body">
		<form method="post" action="#" name="form-edit" id="form-edit" class="form-horizontal">
			@csrf
			<input id="ID" type="hidden" name="id" value="{{$id}}"/>
			<input id="Model" type="hidden" name="model" value="{{$model}}"/>
			<div class="form-group row">
				<label class="col-lg-2 col-form-label"
				       for="source">Source: <strong class="red">*</strong></label>
				<div class="col-lg-4">
					<select name="source" id="source" data-placeholder="Select Source" class="form-control"
					        data-module="select2">
						<option value=""></option>
						<option value="Cash">Cash</option>
						<option value="Bank">Bank</option>
						<option value="Easypaisa">EasyPaisa</option>
						<option value="Jazzcash">JazzCash</option>
					</select>
				</div>
			</div>
			<p class="red" id="cashMsg" style="display: none">You Selected Cash Method. It has no further field Click on Add
				Payment Method</p>
			<div id="advance" style="display: none">
				<div class="form-group row">
					<label class="col-lg-2 col-form-label"
					       for="source">Name: <strong class="red">*</strong></label>
					<div class="col-lg-4">
						<input type="text" class="form-control" name="name" id="name" placeholder="Meezan Bank / Telenor">
					</div>
					<label class="col-lg-2 col-form-label"
					       for="source">Account Title: <strong class="red">*</strong></label>
					<div class="col-lg-4">
						<input type="text" class="form-control" name="account_title" id="account_title"
						       placeholder="Xpertz Dev IT Solution / Haroon Yousaf">
					</div>
				</div>
				<div class="form-group row">
					<label class="col-lg-2 col-form-label"
					       for="source">Account Branch: <strong class="red">*</strong></label>
					<div class="col-lg-4">
						<input type="text" class="form-control" name="account_branch" id="account_branch"
						       placeholder="180200000000 / 0333333333">
					</div>
					<label class="col-lg-2 col-form-label"
					       for="source">Account Number: <strong class="red">*</strong></label>
					<div class="col-lg-4">
						<input type="text" class="form-control" name="account_number" id="account_number"
						       placeholder="180200000000 / 0333333333">
					</div>
				</div>
			</div>
		</form>
		<div class="box-footer text-right">
			<div class="btn-group">
				<input type="submit" id="form-submit" name="form-submit" class="btn btn-primary" form="form-edit"
				       value="Add Customer">
				<input type="reset" name="form-reset" id="form-reset" class="btn btn-default" form="form-edit">
				<input type="button" name="form-cancel" id="form-cancel" class="btn btn-warning" value="Cancel">
			</div>
		</div>
	</div>

</div>


