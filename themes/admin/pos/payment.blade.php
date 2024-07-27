<div class="card border-dark" id="paymentContainer" style="display: none">
	<div class="card-header bg-dark text-white header-elements-inline">
		<h6 class="card-title">Payment Method</h6>
		<div class="header-elements">
			<button type="button" style="display: none; float:right; bottom: 5px;" data-action="backToPayment"
			        id="backBtn" name=""
			        class="btn btn-primary">Back
			</button>
		</div>
	</div>
	<div class="card-body">
		<div class="row mb-3">
			<div class="col-md-6">
				<label for="date" class="col-md-6 col-form-label">Date:</label>
				<div class="col-md-6">
					<input type="date" name="date" value="{{date('Y-m-d')}}" id="date" required class="form-control"/>

				</div>
			</div>

			<div class="col-md-6">
				<div class="table-responsive">
					<table id="table1" class="table table-striped table-hover">
						<tr>
							<th> Total Price</th>
							<td><h6 id="price"></h6></td>
						</tr>
						<tr>
							<th>
								<select onchange="proceed()" id="disc" name="dis"
								        data-placeholder="Select Discount" class="form-control"
								        data-module="select2">
									<option value="rupee"> Discount by Rs.</option>
									<option value="per"> Discount by %</option>
								</select>
							</th>
							<td><input id="discount" value="0" onblur="proceed()" type="text"
							           name="discount"
							           class="form-control"></td>
						</tr>
						<tr>
							<th>Delivery Charges</th>
							<td><input id="delivery" value="0" onblur="proceed()" type="text"
							           name="delivery_charges" class="form-control"></td>
						</tr>
						<tr>
							<th> Net Amount</th>
							<td><h6 id="netAmount"></h6></td>
						</tr>
					</table>
				</div>

			</div>
		</div>
		<div class="row">

			<div class="col-md-6">
				<div class="card border-green">
					<div class="card-header bg-green text-white header-elements-inline">
						<h6 class="card-title">Quick Method</h6>
					</div>

					<div class="card-body">

						<label class="col-form-label">Paid:</label>
						<input type="text" name="paid" id="paid" value="0" class="form-control" placeholder="Enter Paid Amount"/>


						<label class="col-form-label">Change / Debit:</label>
						<input type="text" disabled name="change" id="change" class="form-control" placeholder="Change / Debit"/>

					</div>
				</div>
			</div>
			{{--<div class="col-md-6" id="advanceMethod">
							<div class="card border-orange">
											<div class="card-header bg-orange-600 text-white header-elements-inline">
															<h6 class="card-title">Advance Method</h6>
											</div>

											<div class="card-body">
															<label class="col-form-label">No of Installments:</label>
															<input type="number" name="installment" id="installment" class="form-control" min="2" value="2"/>

															<label class="col-form-label">Scheduled On:</label>
															<input type="date" name="schedule_on" id="schedule_on" value="{{date('Y-m-d')}}" class="form-control"/>
															<label class="col-form-label"
																						for="status">Payment Method:</label>
															<select name="payment_id" id="payments" data-placeholder="Select Payment Type"
																							class="form-control select2"></select>
															</select>
															<label class="col-form-label">Amount:</label>
															<input type="text" name="amount" id="amount" class="form-control"/>

											</div>
							</div>
			</div>--}}
		</div>


	</div>
	<div class="card-footer bg-white">
		<button type="button" style="float:right;bottom: 5px;" data-action="save" id="saveBtn" class="btn btn-success"> Save
		</button>
	</div>
</div>


@push('footer')
	<script>

	 $('#paid').on('keyup', function () {
		 let net = $('#netAmount').text()
		 let paid = $(this).val()
		 let change = parseFloat(paid) - parseFloat(net)
		 $('#change').val(change)
	 })
	</script>
@endpush()
