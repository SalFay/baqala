<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Job Email</title>

	<link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet"
	      type="text/css">

	<title>Job</title>
</head>
<body
		style="text-align: center; font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol'">

<h3>{{$body}}</h3>

<p>Customer: {{$job->customer->full_name}}</p>
<p>Address: {{$job->customer->address}}</p>
<p>Stage: {{$job->job->name}}</p>
<p>Thank You.</p>
</body>
</html>
