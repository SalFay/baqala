<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Baqala POS</title>
    <link rel="icon" href="/favicon.ico">
    @routes
    @viteReactRefresh
    @vite(['resources/js/pos-app/src/main.jsx'])
</head>
<body>
    <div id="root"></div>
</body>
</html>
