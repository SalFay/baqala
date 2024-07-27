@push('head')

@endpush

@push('footer')
    <script src="{{asset('themes/default/ckeditor/ckeditor.js')}}"></script>
    <script src="{{asset('themes/default/ckeditor/adapters/jquery.js')}}"></script>
    <script>
        let options = {
            toolbarGroups: [{
                "name": "basicstyles",
                "groups": ["basicstyles"]
            },
                {
                    "name": "links",
                    "groups": ["links"]
                },
                {
                    "name": "paragraph",
                    "groups": ["list", "blocks"]
                },
                {
                    "name": "document",
                    "groups": ["mode"]
                },
                {
                    "name": "insert",
                    "groups": ["insert"]
                },
                {
                    "name": "styles",
                    "groups": ["styles"]
                },
                {
                    "name": "about",
                    "groups": ["about"]
                }
            ],
            // Remove the redundant buttons from toolbar groups defined above.
            removeButtons: 'Underline,Strike,Subscript,Superscript,Anchor,Styles,Specialchar'
        };
        $('.editor').ckeditor(options);
    </script>
@endpush
