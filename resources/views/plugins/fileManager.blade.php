@push('footer')
    <script src="{{asset('vendor/laravel-filemanager/js/lfm.js')}}"></script>
    <script>
        $('[data-media="files"]').filemanager('file');
        $('[data-media="images"]').filemanager('image');
    </script>
@endpush
