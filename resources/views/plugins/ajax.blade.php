@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('footer')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
@endpush
