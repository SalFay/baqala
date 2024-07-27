<div class="card border-dark">
	<div class="card-header bg-dark text-white header-elements-inline">
		<h6 class="card-title">Payment Method</h6>
	</div>
	<div class="card-body">
		<label for="date" class="col-md-6 col-form-label">Invoice No:</label>
		<input type="text" name="invoice_no" id="invoice_no" class="form-control"
		       placeholder="Invoice No"/>
		<label for="date" class="col-md-6 col-form-label">Date:</label>
		<input type="date" name="date" value="{{date('Y-m-d')}}" id="date" required class="form-control"/>
		<label class="col-form-label">Total:</label>
		<input type="text" name="price" id="price" readonly class="form-control" placeholder="Total"/>
		<label class="col-form-label">Delivery Charges:</label>
		<input id="delivery" value="0" onblur="proceed()" type="text"
		       name="delivery_charges" class="form-control">
		<label class="col-form-label">Discount Type:</label>

		<select onchange="proceed()" id="disc" name="dis"
		        data-placeholder="Select Discount" class="form-control"
		        data-module="select2">
			<option value="rupee"> Discount by Rs.</option>
			<option value="per"> Discount by %</option>
		</select>
		<label class="col-form-label">Discount:</label>
		<input id="discount" value="0" onblur="proceed()" type="text"
		       name="discount"
		       class="form-control">

		<label class="col-form-label">Net Amount:</label>
		<input type="text" name="netAmount" id="netAmount" readonly class="form-control" placeholder="Net Amount"/>
		<label class="col-form-label">Paid:</label>
		<input type="text" name="paid" id="paid" value="0" class="form-control" placeholder="Enter Paid Amount"/>
		<div class="card-footer bg-white">
			<button type="button" style="float:right;bottom: 5px;" data-action="save" id="saveBtn" class="btn btn-success"> Save
			</button>
		</div>
	</div>

</div>


