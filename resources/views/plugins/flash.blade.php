@php($success = session('_success'))
@php($error = session('_danger'))
@php($alerts = session('_warning'))
@php($info = session('_info'))

@if(!empty($error) || $errors->any())
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-ban"></i> Error!</h4>
        @if(empty($error[1]) && !$errors->any())
            {{$error[0]}}
        @else
            <ul>
                @if(!empty($error))
                    <li>{!! implode('</li><li>',$error) !!}</li>
                @endif
                @foreach($errors->all() as $validation)
                    <li>{{$validation}}</li>
                @endforeach
            </ul>
        @endif
    </div>
@endif
@if(!empty($success))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fas fa-check"></i> OK!</h4>
        @if(empty($success[1]))
            {{$success[0]}}
        @else
            <ul>
                <li>{!! implode('</li><li>',$success) !!}</li>
            </ul>
        @endif
    </div>
@endif
@if(!empty($alerts))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-warning"></i> Warning!</h4>
        @if(empty($alerts[1]))
            {{$alerts[0]}}
        @else
            <ul>
                <li>{!! implode('</li><li>',$alerts) !!}</li>
            </ul>
        @endif
    </div>
@endif
@if(!empty($info))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Info!</h4>
        @if(empty($info[1]))
            {{$info[0]}}
        @else
            <ul>
                <li>{!! implode('</li><li>',$info) !!}</li>
            </ul>
        @endif
    </div>
@endif
@php(session()->forget('_success'))
@php(session()->forget('_danger'))
@php(session()->forget('_warning'))
@php(session()->forget('_info'))
