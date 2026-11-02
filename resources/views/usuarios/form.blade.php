@extends('layouts.app')

@php($editando = $usuario->exists)
@section('titulo', $editando ? 'Editar usuario' : 'Nuevo usuario')
@section('subtitulo', 'La contraseña debe tener al menos 8 caracteres, con letras y números.')

@section('acciones')
    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
@endsection

@section('contenido')
<div class="row">
    <div class="col-12 col-xl-7">
        <div class="card">
            <div class="card-body p-4 form-tactil">
                <form method="POST" data-un-envio action="{{ $editando ? route('usuarios.update', $usuario) : route('usuarios.store') }}" autocomplete="off">
                    @csrf
                    @if ($editando) @method('PUT') @endif
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="nombre" class="form-label">Nombre completo</label>
                            <input type="text" id="nombre" name="nombre" maxlength="100" value="{{ old('nombre', $usuario->nombre) }}" class="form-control @error('nombre') is-invalid @enderror" required>
                        </div>
                        <div class="col-12 col-md-7">
                            <label for="correo" class="form-label">Correo electrónico (usuario)</label>
                            <input type="email" id="correo" name="correo" maxlength="150" value="{{ old('correo', $usuario->correo) }}" class="form-control @error('correo') is-invalid @enderror" required>
                        </div>
                        <div class="col-12 col-md-5">
                            <label for="rol_id" class="form-label">Rol</label>
                            <select id="rol_id" name="rol_id" class="form-select @error('rol_id') is-invalid @enderror" required
                                    @disabled($editando && $usuario->is(auth()->user()))>
                                @foreach ($roles as $r)
                                    <option value="{{ $r->id }}" @selected((string) old('rol_id', $usuario->rol_id) === (string) $r->id)>{{ $r->nombre }}</option>
                                @endforeach
                            </select>
                            @if ($editando && $usuario->is(auth()->user()))
                                <input type="hidden" name="rol_id" value="{{ $usuario->rol_id }}">
                                <div class="form-text">No puede cambiar el rol de su propia cuenta.</div>
                            @endif
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="password" class="form-label">{{ $editando ? 'Nueva contraseña (opcional)' : 'Contraseña temporal' }}</label>
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" @required(! $editando)>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password" @required(! $editando)>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-vino btn-lg px-5"><i class="bi bi-save me-2"></i>{{ $editando ? 'Guardar cambios' : 'Crear cuenta' }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
