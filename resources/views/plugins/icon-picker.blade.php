@push('head')
    <link rel="stylesheet" href="{{asset('themes/default/icon-picker/css/fontawesome-iconpicker.min.css')}}">
@endpush
@push('footer')
    <script src="{{asset('themes/default/icon-picker/js/fontawesome-iconpicker.min.js')}}"></script>
    <script>
        $(document).ready(function(){
            $('[data-icon-picker]').iconpicker({
                title:false,
                hideOnSelect:true,
            });
        });
    </script>
@endpush
