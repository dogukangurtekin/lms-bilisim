<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bilişim Kod')</title>
    <style>
        html, body { margin: 0; padding: 0; min-height: 100%; }
        body { font-family: "Nunito", "Segoe UI", system-ui, -apple-system, Arial, sans-serif; background: linear-gradient(160deg, #eef4ff, #dbe8ff); padding: 12px; box-sizing: border-box; }
    </style>
</head>
<body>
@yield('content')
@stack('scripts')
</body>
</html>
