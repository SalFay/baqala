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
        <h6 class="card-title">Sign Document</h6>
    </div>

    <div class="card-body">
        <div id="img" style="height: 80vh"></div>

    </div>
</div>

<!-- /page content -->
<script src="{{asset('js/app.js')}}"></script>
<script>

    HelloSignClient.open('{!! $url !!}', {
        clientId: '{{config("app.api.hello_sign.client_id")}}',
        skipDomainVerification: true,
        container: document.getElementById('img'),
        debug: true
    })
    HelloSignClient.on('sign', (data) => {
        $.ajax({
            url: '{{route('signature.signed')}}',
            type: 'post',
            data: { sign_id: data.signatureId, '_token': "{{ csrf_token() }}", },
            dataType: 'json',
            success: function (res) {
                if (res.status === 'ok') {
                    ui.successMessage(res.message)
                    window.location.replace('{{route('thanks')}}')
                    return true
                }
                ui.errorMessage(res.message)
            },
            error: function (res) {

                ui.ajaxError(res, 0)
            }
        })
        console.log('The document has been signed!')
        console.log('Signature ID: ' + data.signatureId)
    })
</script>
</body>
</html>

