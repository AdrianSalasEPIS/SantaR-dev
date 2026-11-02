@extends('layouts.app')

@section('titulo', 'Existencias')
@section('subtitulo', 'Stock actual de cada producto e implemento, con sus alertas.')

@section('contenido')
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('existencias.index') }}" class="btn btn-sm {{ empty($filtros['estado']) ? 'btn-primary' : 'btn-outline-secondary' }}">Todos <span class="badge text-bg-light ms-1">{{ $resumen['total'] }}</span></a>
    <a href="{{ route('existencias.index', ['estado' => 'alerta']) }}" class="btn btn-sm {{ ($filtros['estado'] ?? '') === 'alerta' ? 'btn-warning' : 'btn-outline-secondary' }}"><i class="bi bi-exclamation-triangle me-1"></i>En alerta <span class="badge text-bg-light ms-1">{{ $resumen['alerta'] }}</span></a>
    <a href="{{ route('existencias.index', ['estado' => 'agotado']) }}" class="btn btn-sm {{ ($filtros['estado'] ?? '') === 'agotado' ? 'btn-danger' : 'btn-outline-secondary' }}"><i class="bi bi-x-octagon me-1"></i>Agotados <span class="badge text-bg-light ms-1">{{ $resumen['agotados'] }}</span></a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-12 col-md-4">
                <label class="form-label small mb-1" for="buscar">Buscar</label>
                <input type="search" id="buscar" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" class="form-control" placeholder="Nombre del producto">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1" for="categoria_id">Categoría</label>
                <select id="categoria_id" name="categoria_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach ($categorias as $c)
                        <option value="{{ $c->id }}" @selected(($filtros['categoria_id'] ?? '') == $c->id)>{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1" for="tipo">Tipo</label>
                <select id="tipo" name="tipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="PRODUCTO" @selected(($filtros['tipo'] ?? '') === 'PRODUCTO')>Productos</option>
                    <option value="IMPLEMENTO" @selected(($filtros['tipo'] ?? '') === 'IMPLEMENTO')>Implementos</option>
                </select>
            </div>
            <input type="hidden" name="estado" value="{{ $filtros['estado'] ?? '' }}">
            <div class="col-12 col-md-3 d-flex gap-2">
                <button class="btn btn-vino flex-grow-1"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="{{ route('existencias.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th></th><th>Producto</th><th>Categoría</th><th>Tipo</th><th class="text-end">Stock actual</th><th class="text-end">Mínimo</th><th class="text-center">Estado</th></tr></thead>
            <tbody>
            @forelse ($productos as $p)
                <tr>
                    <td style="width:64px">
                        @if ($p->imagenUrl())<img src="{{ $p->imagenUrl() }}" class="miniatura" alt="">
                        @else<span class="miniatura-vacia"><i class="bi {{ $p->esImplemento() ? 'bi-cup' : 'bi-cup-straw' }}"></i></span>@endif
                    </td>
                    <td><div class="fw-bold">{{ $p->nombre }}</div><small class="text-secondary">{{ $p->caracteristicas }}</small></td>
                    <td class="small">{{ $p->categoria->nombre }}</td>
                    <td class="small">{{ \App\Models\Producto::TIPOS[$p->tipo] }}</td>
                    <td class="text-end fs-5 fw-bold">{{ $p->stock_actual }}</td>
                    <td class="text-end text-secondary">{{ $p->stock_minimo }}</td>
                    <td class="text-center"><span class="estado estado-{{ strtolower($p->estado()) }}">{{ $p->estado() }}</span></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-secondary py-5">No se encontraron productos con esos filtros.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($productos->hasPages())<div class="card-footer">{{ $productos->links() }}</div>@endif
</div>
@endsection
