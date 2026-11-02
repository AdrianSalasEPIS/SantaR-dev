@extends('layouts.app')

@section('titulo', 'Mi cuenta')
@section('subtitulo', 'Cambie su contraseña. Debe tener al menos 8 caracteres, con letras y números.')

@section('contenido')
<div class="row g-4">
    <div class="col-12 col-xl-4">
        <div class="card text-center">
            <div class="card-body py-4">
                <span class="avatar avatar-lg d-flex mx-auto mb-3">{{ $usuario->iniciales() }}</span>
                <h2 class="h4 mb-1">{{ $usuario->nombre }}</h2>
                <div class="text-secondary">{{ $usuario->correo }}</div>
                <span class="badge bg-vino mt-2">{{ $usuario->rol->nombre }}</span>
                <p class="small text-secondary mt-3 mb-0">El correo es solo su usuario de acceso: SantaR no envía mensajes. Si olvida su contraseña, el administrador puede restablecerla.</p>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-key me-2 text-oro"></i>Cambiar contraseña</div>
            <div class="card-body p-4 form-tactil">
                <form method="POST" data-un-envio action="{{ route('perfil.contrasena') }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label for="password_actual" class="form-label">Contraseña actual</label>
                        <input type="password" id="password_actual" name="password_actual" class="form-control @error('password_actual') is-invalid @enderror" autocomplete="current-password" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Nueva contraseña</label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" required>
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Repita la nueva contraseña</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password" required>
                    </div>
                    <button class="btn btn-vino btn-lg w-100"><i class="bi bi-shield-lock me-2"></i>Actualizar contraseña</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
