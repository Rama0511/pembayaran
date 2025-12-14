<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="user-name" content="{{ auth()->user()->name }}">
    @endauth
    
    <title>Sistem Pembayaran</title>

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/main.jsx'])
</head>
<body>
    <div id="app"></div>
    @auth
    <script>
        window.appUser = @json(auth()->user()->name);
        window.isAuthenticated = true;
    </script>
    @else
    <script>
        window.isAuthenticated = false;
    </script>
    @endauth
</body>
</html>
