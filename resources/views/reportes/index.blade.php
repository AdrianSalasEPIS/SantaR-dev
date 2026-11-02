@extends('layouts.app')

@section('titulo', 'Reportes')
@section('subtitulo', 'Existencias, movimientos, abastecimientos e incidencias, con filtros por fecha y categoría. Expórtelos para el propietario.')

@php($consulta = array_filter($filtros, fn ($v) => filled($v)))

@section('acciones')
    <a href="{{ route('reportes.exportar', ['formato' => 'pdf'] + $consulta) }}" class="btn btn-vino"><i class="bi bi-file-earmark-pdf me-1"></i>Exportar PDF</a>
    <a href="{{ route('reportes.exportar', ['formato' => 'excel'] + $consulta) }}" class="btn btn-oro"><i class="bi bi-file-earmark-excel me-1"></i>Exportar Excel</a>
@endsection

@section('contenido')
<div class="d-flex flex-wrap gap-2 mb-3" role="tablist" aria-label="Tipo de reporte">
    @foreach ($tipos as $clave => $nombre)
        <a href="{{ route('reportes.index', ['tipo' => $clave] + \Illuminate\Support\Arr::except($consulta, 'tipo')) }}"
           class="btn {{ $filtros['tipo'] === $clave ? 'btn-vino' : 'btn-outline-secondary' }}"
           @if ($filtros['tipo'] === $clave) aria-current="page" @endif>{{ $nombre }}</a>
    @endforeach
</div>

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="GET">
            <input type="hidden" name="tipo" value="{{ $filtros['tipo'] }}">
            @unless ($filtros['tipo'] === 'existencias')
                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1" for="desde">Desde</label>
                    <input type="date" id="desde" name="desde" value="{{ $filtros['desde'] }}" class="form-control">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1" for="hasta">Hasta</label>
                    <input type="date" id="hasta" name="hasta" value="{{ $filtros['hasta'] }}" class="form-control">
                </div>
            @endunless
            <div class="col-12 col-md-3">
                <label class="form-label small mb-1" for="categoria_id">Categoría</label>
                <select id="categoria_id" name="categoria_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach ($categorias as $c)<option value="{{ $c->id }}" @selected((string) ($filtros['categoria_id'] ?? '') === (string) $c->id)>{{ $c->nombre }}</option>@endforeach
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button class="btn btn-vino flex-grow-1"><i class="bi bi-funnel me-1"></i>Generar</button>
                <a href="{{ route('reportes.index', ['tipo' => $filtros['tipo']]) }}" class="btn btn-outline-secondary" title="Limpiar filtros" aria-label="Limpiar filtros"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span><i class="bi bi-clipboard-data me-2 text-oro"></i>{{ $reporte['titulo'] }}</span>
        <small class="text-secondary">{{ $reporte['periodo'] }}{{ $reporte['categoria'] ? ' · '.$reporte['categoria'] : '' }}</small>
    </div>
    <div class="card-body border-bottom">
        <div class="row g-3">
            @foreach ($reporte['resumen'] as $etiqueta => $valor)
                <div class="col-6 col-lg-3">
                    <div class="small text-secondary">{{ $etiqueta }}</div>
                    <div class="fs-4 fw-bold text-vino">{{ $valor }}</div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr>@foreach ($reporte['columnas'] as $col)<th>{{ $col }}</th>@endforeach</tr></thead>
            <tbody>
            @forelse (array_slice($reporte['filas'], 0, $limite) as $fila)
                <tr>@foreach ($fila as $celda)<td class="small">{{ $celda }}</td>@endforeach</tr>
            @empty
                <tr><td colspan="{{ count($reporte['columnas']) }}" class="text-center text-secondary py-5">No hay datos para el período y los filtros elegidos.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if (count($reporte['filas']) > $limite)
        <div class="card-footer small text-secondary">Se muestran las primeras {{ $limite }} filas de {{ count($reporte['filas']) }}. La exportación a PDF o Excel incluye todas.</div>
    @endif
</div>
@endsection
