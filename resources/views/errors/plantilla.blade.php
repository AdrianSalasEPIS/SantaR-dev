{{-- Plantilla común de las páginas de error: nunca muestra detalles técnicos al usuario. --}}
@extends('layouts.guest')

@section('titulo', $titulo)

@section('contenido')
<main class="login-fondo">
    <div class="login-tarjeta">
        <img src="{{ asset('img/santar-logo.svg') }}" alt="" class="logo-login" width="96" height="96" style="width:96px;height:96px">
        <p class="text-oro fw-bold mb-1">Error {{ $codigo }}</p>
        <h1 class="h3 text-vino">{{ $titulo }}</h1>
        <p class="text-secondary">{{ $mensaje }}</p>
        <a href="{{ url('/panel') }}" class="btn btn-vino w-100"><i class="bi bi-house me-2"></i>Volver al inicio</a>
    </div>
</main>
@endsection
