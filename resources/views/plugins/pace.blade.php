@push('head')
    <link rel="stylesheet" href="{{asset('themes/default/pace/pace.css')}}"/>
@endpush

@push('footer')
    <script data-pace-options='{ "ajax": true }' src="{{asset('themes/default/pace/pace.js')}}"></script>
@endpush
