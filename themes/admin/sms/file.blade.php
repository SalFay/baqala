@extends('admin.layouts.admin')
@section('page-title','SMS / EMAIL ')
@section('heading','SMS / EMAIL ')
@section('breadcrumbs')
	<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a>
	</li>
	<li class="breadcrumb-item"><a href="{{route('admin.sms.file')}}">Send SMS / EMAIL </a>
	</li>
@endsection
<style>
    .bootstrap-tagsinput .tag {
        background: cornflowerblue;
        padding: 4px;
        border-radius: 5px;
        font-size: 14px;
    }

    .bootstrap-tagsinput {
        display: flex !important;
        padding: 8px 6px !important;
    }
</style>
@section('content')
	<section id="section-overview">
		<div class="card" id="section-profile">
			<div class="card-header bg-teal-400 header-elements-inline">

				<h4 class="card-title">Send SMS / EMAIL from Excel</h4>
			</div>
			<div class="card-body">

				<form method="POST" action="#" enctype="multipart/form-data" id="form-sms">
					@csrf
					<div class="d-flex">
						<div class="form-check form-check-primary">
							<input type="radio" name="type" class="form-check-input" value="EMAIL" checked>
							<label class="form-check-label" for="email">Send Email</label>
						</div>&nbsp; &nbsp; &nbsp;
						<div class="form-check form-check-primary">
							<input type="radio" name="type" class="form-check-input" value="SMS">
							<label class="form-check-label" for="sms">Send SMS</label>
						</div>

					</div>
					<br>
					<div class="col-md-12">
						<label class="form-label" for="file">
							<strong>Upload Excel File</strong> (<span><a href="{{asset('sample-sms.xlsx')}}">SMS Sample</a></span>) and
							(<span><a href="{{asset('sample-email.xlsx')}}">EMAIL Sample</a></span>)
						</label>
						<input type="file" class="form-control" name="file"/>
					</div>
					<br>
					<div class="col-md-12">
						<label class="form-label" for="file">
							<strong>Enter Numbers / Emails (e.g. 03339471086,03339471087 OR
								haroonyousaf80@gmail.com,haroon@ydi.edu.pk)</strong>
						</label>
						<input type="text" class="form-control" name="numbers" id="tags-input"/>
					</div>
					<br>
					<div class="col-md-12">
						<label class="form-label" for="file">
							<strong>Message</strong>
						</label>
						<textarea type="text" class="form-control" id="message" name="message"
						          placeholder="Write Message"
						></textarea>
					</div>
					<br>

					<button id="send-message" class="btn btn-primary">Send SMS / Email</button>

				</form>
			</div>
		</div>
	</section>
@endsection

@push('footer')
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css"
	      integrity="sha512-xmGTNt20S0t62wHLmQec2DauG9T+owP9e6VU8GigI0anN7OXLip9i7IwEhelasml2osdxX71XcYm6BQunTQeQg=="
	      crossorigin="anonymous"/>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js"
	        integrity="sha512-9UR1ynHntZdqHnwXKTaOm1s6V9fExqejKvg5XMawEMToW4sSw+3jtLrYfZPijvnwnnE8Uol1O9BcAskoxgec+g=="
	        crossorigin="anonymous"></script>

	<script>
	 let tagInputEle = $('#tags-input')
	 $(document).ready(function () {
		 tagInputEle.tagsinput()
	 })

	 $('body').on('submit', '#form-sms', function (e) {
		 e.preventDefault()
		 let $form = $(this)
		 let form_data = new FormData($form[0])
		 $.ajax({
			 url: '{{route('admin.sms.send-message')}}',
			 type: 'post',
			 data: form_data,
			 contentType: false,
			 processData: false,
			 dataType: 'json',
			 success: function (res) {
				 console.log(res)
				 if (res.status === 'ok') {
					 $form[0].reset()
					 tagInputEle.tagsinput('removeAll')
					 ui.successMessage(res.message)
					 return true
				 }

				 ui.errorMessage(res.message)
			 },
			 error: function (res) {
				 ui.ajaxError(res, 0)
			 }
		 })
	 })

	</script>
@endpush()

