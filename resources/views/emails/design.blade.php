<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Design Email</title>

    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet"
          type="text/css">

    <title>Design</title>
</head>
<body style="text-align: center; font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol'">

<h3>Your Design is Ready for {{$customer}}</h3>
{{--<h4>Shade Report and site assessment:</h4>

@foreach($attachments as $file)
    <a href="{{$file}}">{{$file}}</a><br>
@endforeach--}}
<p>{{$body}}</p>
<div style="align-items: center">
    <a style="background-color: #4d9933; /* Green */
  border: none;
  color: white;
  padding: 15px 32px;
  text-align: center;
  border-radius: 8px;
  text-decoration: none;
  width: 200px;
  display: inline-block;
  font-size: 12px;" href="{{$model}}">Click here to see 3D Modelling </a>
    <br> <br>
    <a style="background-color: #4d9933; /* Green */
  border: none;
  color: white;
  padding: 15px 32px;
  text-align: center;
  border-radius: 8px;
    width: 200px;

  text-decoration: none;
  display: inline-block;
  font-size: 12px;"  href="{{$site}}">Click here to see Site Assessment </a>
    <br><br>
    <a style="background-color: #4d9933; /* Green */
  border: none;
  color: white;
  padding: 15px 32px;
  text-align: center;
  border-radius: 8px;
    width: 200px;

  text-decoration: none;
  display: inline-block;
  font-size: 12px;" href="{{$shade}}">Click here to see Shade Report </a>
</div>
<p>Thank You.</p>
</body>
</html>
