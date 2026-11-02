@extends('layouts.app')

@section('titulo', 'Usuarios')
@section('subtitulo', 'Solo el administrador crea cuentas. Las cuentas no se eliminan: se desactivan y conservan su historial.')

@section('acciones')
    <a href="{{ route('usuarios.create') }}" class="btn btn-vino"><i class="bi bi-person-plus me-1"></i>Nuevo usuario</a>
@endsection

@section('contenido')
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Usuario</th><th>Correo</th><th>Rol</th><th>Último acceso</th><th class="text-center">Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            @foreach ($usuarios as $u)
                <tr class="{{ $u->activo ? '' : 'opacity-50' }}">
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm">{{ $u->iniciales() }}</span>
                            <span class="fw-bold">{{ $u->nombre }}</span>
                            @if ($u->is(auth()->user()))<span class="badge text-bg-light border">Usted</span>@endif
                        </div>
                    </td>
                    <td>{{ $u->correo }}</td>
                    <td><span class="badge {{ $u->esAdministrador() ? 'bg-vino' : 'text-bg-secondary' }}">{{ $u->rol->nombre }}</span></td>
                    <td class="small text-secondary">{{ $u->ultimo_acceso?->format('d/m/Y H:i') ?? 'Nunca' }}</td>
                    <td class="text-center"><span class="badge {{ $u->activo ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $u->activo ? 'Activa' : 'Desactivada' }}</span></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('usuarios.edit', $u) }}" class="btn btn-sm btn-outline-primary btn-accion"><i class="bi bi-pencil"></i> Editar</a>
                        @unless ($u->is(auth()->user()))
                            <form method="POST" action="{{ route('usuarios.estado', $u) }}" class="d-inline"
                                  data-confirmar="{{ $u->activo ? '¿Desactivar esta cuenta? Ya no podrá ingresar al sistema.' : '¿Reactivar esta cuenta?' }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-accion {{ $u->activo ? 'btn-outline-danger' : 'btn-outline-success' }}" title="{{ $u->activo ? 'Desactivar cuenta' : 'Reactivar cuenta' }}" aria-label="{{ ($u->activo ? 'Desactivar la cuenta de ' : 'Reactivar la cuenta de ').$u->nombre }}"><i class="bi {{ $u->activo ? 'bi-person-dash' : 'bi-person-check' }}"></i></button>
                            </form>
                        @endunless
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
