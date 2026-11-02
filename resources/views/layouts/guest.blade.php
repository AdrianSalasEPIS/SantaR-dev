<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#2b0a14">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('titulo', 'Iniciar sesión') · SantaR</title>
    <link rel="icon" href="{{ asset('img/santar-logo.svg') }}" type="image/svg+xml">
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    @yield('contenido')
</body>
</html>
