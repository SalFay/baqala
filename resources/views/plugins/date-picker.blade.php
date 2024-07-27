@push('head')
	<link rel="stylesheet" href="{{asset('plugins/js/pickers/flatpickr/flatpickr.min.css')}}">
@endpush

@push('footer')
	<script src="{{asset('plugins/js/pickers/flatpickr/flatpickr.min.js')}}"></script>
	<script src="{{asset('plugins/js/forms/pickers/form-pickers.js')}}"></script>
	<script>
   $(document).ready(function () {
     $('.date-picker').flatpickr({
       altInput: true,
       altFormat: 'F j, Y',
       dateFormat: 'Y-m-d',
     })

   })
	</script>
@endpush
