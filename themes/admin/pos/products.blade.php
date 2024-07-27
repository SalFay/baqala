<div id="productList">
	<label for="barcode"><strong>Search Product:</strong></label>
	<input type="text" name="pid" id="barcode" autocomplete="off" autofocus class="form-control"/>
</div>
@include('plugins.ajax')
@push('footer')
	<script>
	 onScan.attachTo(document, {
		 scanButtonKeyCode: false,
		 scanButtonLongPressTime: 100, //500
		 timeBeforeScanTest: 100, //200
		 avgTimeByChar: 40,
		 minLength: null,
		 suffixKeyCodes: [13],
		 ignoreIfFocusOn: true,
		 preventDefault: true,
		 reactToKeydown: true,
		 reactToPaste: true,
		 singleScanQty: 1,

		 onScan: function (sScanned) { // Alternative to document.addEventListener('scan')
			 let barcode = sScanned
			 $('#barcode').val(barcode)
		 },

		 onKeyDetect: function (iKeyCode) { // output all potentially relevant key events - great for debugging!
			 console.log('Pressed: ' + iKeyCode)
		 }

	 })
	 //onScan.simulate(document, 'sm-2');

	 // Remove onScan.js from a DOM element completely
	 onScan.detachFrom(document)

	 $('#barcode').change(function () {
		 let barcode = $(this).val()
		 if (barcode != '') {
			 let customer = $('#customer_id').val()
			 let vendor = $('#vendor_id').val()
			 let order = $('#order_id').val()
			 $.ajax({
				 url: '{{route('products.get')}}',
				 type: 'post',
				 dataType: 'json',
				 data: { pid: barcode, customer: customer, order: order, vendor: vendor },
				 success: function (res) {
					 if (res.status === 'ok') {
						 ui.successMessage(res.message)
						 refreshCart()
						 $('#barcode').val('').focus()
						 return true
					 }
					 ui.errorMessage(res.message)
				 },
				 error: function (res) {
					 ui.ajaxError(res)
				 }
			 })
		 }

	 })

	</script>
@endpush
