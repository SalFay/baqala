<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Customer Signature - Spark Empower</title>

    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet"
          type="text/css">
    <link rel="icon" href="{{asset('assets/light.png')}}" type="image/png">
    <link href="{{asset('assets/css/app.css')}}" type="text/css" rel="stylesheet">
    <link href="{{asset('assets/css/style.css')}}" type="text/css" rel="stylesheet">
    @stack('head')


</head>

<body>
<div class="card" id="section-overview">

    <div
        class="card-header header-elements-inline">
        <h6 class="card-title">Thank You</h6>
    </div>

    <div class="card-body">
        @if(isset($message))
            <p class="alert alert-danger">{{$message}}</p>
        @else
            <h3>Thank You For Sign the Document</h3>
        @endif
    </div>
</div>

</body>
</html>

