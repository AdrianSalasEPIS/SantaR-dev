@extends('layouts.app')

@section('titulo', 'Bitácora')
@section('subtitulo', 'Registro automático de las acciones realizadas en el sistema: quién, qué, cuándo y desde dónde.')

@section('contenido')
<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1" for="usuario_id">Usuario</label>
                <select id="usuario_id" name="usuario_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($usuarios as $u)<option value="{{ $u->id }}" @selected(($filtros['usuario_id'] ?? '') == $u->id)>{{ $u->nombre }}</option>@endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1" for="modulo">Módulo</label>
                <select id="modulo" name="modulo" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($modulos as $k => $m)<option value="{{ $k }}" @selected(($filtros['modulo'] ?? '') === $k)>{{ $m }}</option>@endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1" for="desde">Desde</label>
                <input type="date" id="desde" name="desde" value="{{ $filtros['desde'] ?? '' }}" class="form-control">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1" for="hasta">Hasta</label>
                <input type="date" id="hasta" name="hasta" value="{{ $filtros['hasta'] ?? '' }}" class="form-control">
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button class="btn btn-vino flex-grow-1"><i class="bi bi-funnel"></i> Filtrar</button>
                <a href="{{ route('bitacora.index') }}" class="btn btn-outline-secondary" title="Limpiar"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Fecha y hora</th><th>Usuario</th><th>Módulo</th><th>Acción</th><th>Detalle</th><th>IP</th></tr></thead>
            <tbody>
            @forelse ($registros as $r)
                <tr>
                    <td class="text-nowrap small">{{ $r->fecha_hora->format('d/m/Y H:i:s') }}</td>
                    <td class="small fw-semibold">{{ $r->usuario?->nombre ?? '—' }}</td>
                    <td><span class="badge text-bg-light border">{{ $modulos[$r->modulo] ?? $r->modulo }}</span></td>
                    <td class="small">{{ ucfirst($r->accion) }}</td>
                    <td class="small text-secondary">{{ $r->detalle }}</td>
                    <td class="small text-secondary">{{ $r->ip }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-secondary py-5">No hay registros con esos filtros.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($registros->hasPages())<div class="card-footer">{{ $registros->links() }}</div>@endif
</div>
@endsection
