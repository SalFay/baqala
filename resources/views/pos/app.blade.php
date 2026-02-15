<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Baqala POS</title>
    <link rel="icon" type="image/svg+xml" href="/pos/vite.svg">
    @php
        $manifestPath = public_path('pos/.vite/manifest.json');
        $manifest = file_exists($manifestPath)
            ? json_decode(file_get_contents($manifestPath), true)
            : null;
        $entry = $manifest['index.html'] ?? null;
    @endphp
    @if($entry)
        @if(!empty($entry['css']))
            @foreach($entry['css'] as $css)
                <link rel="stylesheet" href="/pos/{{ $css }}">
            @endforeach
        @endif
        @if(!empty($entry['imports']))
            @foreach($entry['imports'] as $import)
                @php $chunk = $manifest[$import] ?? null; @endphp
                @if($chunk)
                    <link rel="modulepreload" crossorigin href="/pos/{{ $chunk['file'] }}">
                @endif
            @endforeach
        @endif
        <script type="module" crossorigin src="/pos/{{ $entry['file'] }}"></script>
    @endif
</head>
<body>
    <div id="root"></div>
</body>
</html>
