<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#2b0a14">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('titulo') · SantaR</title>
    <link rel="icon" href="{{ asset('img/santar-logo.svg') }}" type="image/svg+xml">
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body data-inactividad="{{ config('session.lifetime') }}">
@php($usuario = auth()->user())
<a href="#contenido" class="saltar-contenido btn btn-oro">Saltar al contenido</a>

<aside class="sr-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="menu-lateral" aria-label="Menú principal">
    <div class="d-flex align-items-center justify-content-between">
        <a href="{{ route('panel') }}" class="marca-menu flex-grow-1">
            <img src="{{ asset('img/santar-logo.svg') }}" alt="">
            <span><strong>SantaR</strong><small>Santa Rita VIP</small></span>
        </a>
        <button type="button" class="btn-close btn-close-white d-lg-none me-3" data-bs-dismiss="offcanvas" data-bs-target="#menu-lateral" aria-label="Cerrar menú"></button>
    </div>
    @include('partials.menu')
</aside>

<div class="sr-contenido d-flex flex-column min-vh-100">
    <header class="sr-topbar sticky-top">
        <div class="container-fluid px-3 px-lg-4 py-2 d-flex align-items-center gap-3">
            <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#menu-lateral" aria-controls="menu-lateral" aria-label="Abrir menú">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div class="small text-secondary d-none d-sm-block">
                <i class="bi bi-calendar3 me-1"></i>{{ ucfirst(now()->translatedFormat('l, d \d\e F \d\e Y')) }}
            </div>
            <div class="ms-auto dropdown">
                <button class="btn btn-link text-decoration-none d-flex align-items-center gap-2 p-0" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Opciones de la cuenta">
                    <span class="text-end lh-sm d-none d-sm-block">
                        <span class="fw-bold text-dark d-block">{{ $usuario->nombre }}</span>
                        <small class="text-secondary">{{ $usuario->rol->nombre }}</small>
                    </span>
                    <span class="avatar">{{ $usuario->iniciales() }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><a class="dropdown-item py-2" href="{{ route('perfil.edit') }}"><i class="bi bi-person-gear me-2"></i>Mi cuenta</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <button type="submit" class="dropdown-item py-2 text-danger" form="form-salir"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</button>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <main id="contenido" class="container-fluid px-3 px-lg-4 py-4 flex-grow-1">
        <div class="encabezado-pagina d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <h1>@yield('titulo')</h1>
                @hasSection('subtitulo')<p>@yield('subtitulo')</p>@endif
            </div>
            <div class="d-flex flex-wrap gap-2">@yield('acciones')</div>
        </div>

        @include('partials.mensajes')
        @yield('contenido')
    </main>

    <footer class="pie-app text-center py-3">
        SantaR · Santa Rita VIP «Fina Herencia» · Desarrollado por Nexora · {{ now()->year }}
    </footer>
</div>

<form id="form-salir" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="inactividad" value="0">
</form>
</body>
</html>
