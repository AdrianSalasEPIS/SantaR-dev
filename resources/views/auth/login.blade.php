@extends('layouts.guest')

@section('titulo', 'Iniciar sesión')

@section('contenido')
<main class="login-fondo">
    <div class="login-tarjeta">
        <h1 class="marca">Santa<span>R</span></h1>
        <p class="bienvenida">Sistema de inventario<br><span class="text-nowrap">Santa Rita VIP «Fina Herencia»</span></p>

        <img src="{{ asset('img/santar-logo.svg') }}" alt="Logo de SantaR: copa de vino" class="logo-login">

        <div class="separador">Iniciar sesión</div>

        @if (session('aviso'))
            <div class="alert alert-warning text-start small py-2" role="status"><i class="bi bi-clock-history me-1"></i>{{ session('aviso') }}</div>
        @endif
        @if (session('estado'))
            <div class="alert alert-success text-start small py-2" role="status"><i class="bi bi-check-circle me-1"></i>{{ session('estado') }}</div>
        @endif
        @error('correo')
            <div class="alert alert-danger text-start small py-2" role="alert"><i class="bi bi-shield-exclamation me-1"></i>{{ $message }}</div>
        @enderror

        <form method="POST" action="{{ route('login.ingresar') }}" novalidate data-un-envio>
            @csrf
            <div class="mb-3">
                <label for="correo" class="form-label">Usuario (correo)</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="email" id="correo" name="correo" value="{{ old('correo') }}" maxlength="150"
                           class="form-control @error('correo') is-invalid @enderror"
                           placeholder="correo@ejemplo.com" autocomplete="username" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Contraseña</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" id="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="••••••••" autocomplete="current-password" required>
                    <button class="btn btn-outline-secondary" type="button" data-ver-password="password" aria-pressed="false" aria-label="Mostrar u ocultar la contraseña">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-vino btn-lg w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
            </button>
        </form>

        <p class="small text-secondary mt-3 mb-0"><i class="bi bi-question-circle me-1"></i>¿Olvidó su contraseña? Solicite al administrador que la restablezca.</p>
        <p class="pie-login mb-0">Las cuentas las crea únicamente el administrador.<br>Desarrollado por Nexora · {{ now()->year }}</p>
    </div>
</main>
@endsection
