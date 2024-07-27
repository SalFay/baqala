@push('head')

@endpush

@push('footer')
    <script src="{{asset('themes/default/ckeditor/ckeditor.js')}}"></script>
    <script src="{{asset('themes/default/ckeditor/adapters/jquery.js')}}"></script>
    <script>
        let options = {
            filebrowserImageBrowseUrl: '/file-manager?type=Images',
            filebrowserImageUploadUrl: '/file-manager/upload?type=Images&_token=',
            filebrowserBrowseUrl: '/file-manager?type=Files',
            filebrowserUploadUrl: '/file-manager/upload?type=Files&_token='
        };
        $('.editor').ckeditor(options);
    </script>
@endpush
