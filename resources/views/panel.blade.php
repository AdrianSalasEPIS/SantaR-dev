@extends('layouts.app')

@section('titulo', 'Panel principal')
@php($hora = (int) now()->format('G'))
@section('subtitulo', ($hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches')).', '.auth()->user()->nombre.'. Este es el resumen de la sede céntrica.')

@section('contenido')
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat vino"><div class="valor">{{ $resumen['productos'] }}</div><div class="etiqueta">Productos de venta activos</div><i class="bi bi-cup-straw fondo" aria-hidden="true"></i></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat oro"><div class="valor">{{ $resumen['enCarta'] }}</div><div class="etiqueta">Visibles en la carta</div><i class="bi bi-book fondo" aria-hidden="true"></i></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat rosado"><div class="valor">{{ $resumen['categorias'] }}</div><div class="etiqueta">Categorías activas</div><i class="bi bi-tags fondo" aria-hidden="true"></i></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat verde"><div class="valor">{{ $resumen['implementos'] }}</div><div class="etiqueta">Implementos del local</div><i class="bi bi-cup fondo" aria-hidden="true"></i></div>
    </div>
</div>

@if (auth()->user()->esAdministrador())
    <h2 class="h5 text-vino mb-3"><i class="bi bi-lightning-charge me-1"></i>Accesos rápidos</h2>
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xxl-3">
            <a href="{{ route('productos.create') }}" class="acceso"><i class="bi bi-plus-circle bg-vino"></i><span>Nuevo producto<small>Vino, licor, snack o implemento</small></span></a>
        </div>
        <div class="col-12 col-md-6 col-xxl-3">
            <a href="{{ route('productos.index') }}" class="acceso"><i class="bi bi-cup-straw bg-oro"></i><span>Productos<small>Precios, imágenes y estado</small></span></a>
        </div>
        <div class="col-12 col-md-6 col-xxl-3">
            <a href="{{ route('categorias.index') }}" class="acceso"><i class="bi bi-tags bg-rosado"></i><span>Categorías<small>Orden de la carta digital</small></span></a>
        </div>
        <div class="col-12 col-md-6 col-xxl-3">
            <a href="{{ route('usuarios.index') }}" class="acceso"><i class="bi bi-people bg-verde"></i><span>Usuarios<small>Cuentas y roles</small></span></a>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="bi bi-clock-history me-2 text-oro"></i>Actividad reciente</span>
            <a href="{{ route('bitacora.index') }}" class="btn btn-sm btn-outline-secondary">Ver bitácora</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Fecha y hora</th><th>Usuario</th><th>Acción</th><th>Detalle</th></tr></thead>
                <tbody>
                @forelse ($actividad as $r)
                    <tr>
                        <td class="small text-secondary text-nowrap">{{ $r->fecha_hora->format('d/m/Y H:i') }}</td>
                        <td class="small fw-semibold">{{ $r->usuario?->nombre ?? '—' }}</td>
                        <td class="small">{{ ucfirst($r->accion) }}</td>
                        <td class="small text-secondary">{{ $r->detalle }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-secondary py-5">Aún no hay actividad registrada.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5 text-secondary">
            <i class="bi bi-info-circle fs-1 d-block mb-2 text-oro"></i>
            Su cuenta tiene el rol <strong>Encargado de sede</strong>. Las opciones de registro del inventario se habilitan en este panel.
        </div>
    </div>
@endif
@endsection
