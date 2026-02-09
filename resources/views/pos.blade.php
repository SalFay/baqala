<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Baqala POS') }}</title>
    @php
        $manifestPath = public_path('build/.vite/manifest.json');
        $manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : null;
        $entry = $manifest['src/main.jsx'] ?? $manifest['index.html'] ?? null;
    @endphp
    @if($entry && isset($entry['css']))
        @foreach($entry['css'] as $css)
            <link rel="stylesheet" href="{{ asset('build/' . $css) }}">
        @endforeach
    @endif
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
    </style>
</head>
<body>
    <div id="root"></div>
    @if($entry)
        <script type="module" src="{{ asset('build/' . $entry['file']) }}"></script>
    @else
        <script>
            document.getElementById('root').innerHTML = '<div style="display:flex;justify-content:center;align-items:center;height:100vh;"><h1>Build not found. Run: yarn build</h1></div>';
        </script>
    @endif
</body>
</html>
