@extends('layouts.app')

@section('titulo', 'Categorías')
@section('subtitulo', 'Agrupan los productos en el inventario y definen el orden de los títulos en la carta digital.')

@section('acciones')
    <a href="{{ route('categorias.create') }}" class="btn btn-vino"><i class="bi bi-plus-lg me-1"></i>Nueva categoría</a>
@endsection

@section('contenido')
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th class="text-center">Orden</th><th>Categoría</th><th>Descripción</th><th class="text-end">Productos activos</th><th class="text-center">Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            @forelse ($categorias as $c)
                <tr class="{{ $c->activo ? '' : 'opacity-50' }}">
                    <td class="text-center"><span class="badge rounded-pill text-bg-light border">{{ $c->orden_carta }}</span></td>
                    <td class="fw-bold">{{ $c->nombre }}</td>
                    <td class="small text-secondary">{{ $c->descripcion ?: '—' }}</td>
                    <td class="text-end">{{ $c->productos_activos }}</td>
                    <td class="text-center"><span class="badge {{ $c->activo ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $c->activo ? 'Activa' : 'Inactiva' }}</span></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('categorias.edit', $c) }}" class="btn btn-sm btn-outline-primary btn-accion"><i class="bi bi-pencil"></i> Editar</a>
                        <form method="POST" action="{{ route('categorias.estado', $c) }}" class="d-inline"
                              data-confirmar="{{ $c->activo ? '¿Desactivar esta categoría?' : '¿Reactivar esta categoría?' }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm btn-accion {{ $c->activo ? 'btn-outline-danger' : 'btn-outline-success' }}" title="{{ $c->activo ? 'Desactivar' : 'Reactivar' }}" aria-label="{{ ($c->activo ? 'Desactivar la categoría ' : 'Reactivar la categoría ').$c->nombre }}"><i class="bi {{ $c->activo ? 'bi-slash-circle' : 'bi-arrow-counterclockwise' }}"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-secondary py-5">No hay categorías registradas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
