@push('head')
    <link rel="stylesheet" type="text/css" href="{{asset('themes/default/select2/css/select2.min.css')}}"/>
    <style>
        .select2-container {
            width: 100% !important;
        }

        .select2-container .select2-selection--single .select2-selection__rendered{
            padding-right: 50px !important
        }

        .select2-search--dropdown:after {
            content: "";
            font-family: "icomoon";

            color: inherit;
            display: block;
            font-size: 0.8125rem;
            margin-top: -0.40625rem;
            line-height: 1;
            opacity: 0.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .select2-container .select2-selection--single {
            box-sizing: border-box;
            cursor: pointer;
            display: block;
            height: 40px !important;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-user-select: none;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 34px !important;
            position: absolute;
            top: 1px;
            right: 1px;
            width: 20px;
        }

    </style>
@endpush

@push('footer')
    <script src="{{asset('themes/default/select2/js/select2.full.min.js')}}"></script>
    <script>
        $(document).ready(function () {
            $('[data-module="select2"]').select2();
        });
    </script>
@endpush
