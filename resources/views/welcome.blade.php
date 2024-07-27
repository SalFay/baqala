<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SMS</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{asset('assets/css/app.css')}}">


</head>

<body>
<div>
    <h1 style="text-align: center; margin-top: 40vh">
        Welcome to <br>
        School Management System </h1>
    <div class="text-center">

        <a type="button" href="{{route('login')}}"
           class="btn btn-lg btn-primary">
            Login
        </a>

    </div>
</div>

<script src="//js.pusher.com/3.0/pusher.min.js"></script>
<script src="{{asset('assets/js/app.js')}}"></script>
</body>
</html>
