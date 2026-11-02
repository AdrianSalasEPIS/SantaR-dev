@extends('layouts.app')

@section('titulo', 'Productos e implementos')
@section('subtitulo', 'Catálogo de la sede: lo que se vende (con su imagen para la carta) y los implementos del local.')

@section('acciones')
    <a href="{{ route('productos.create') }}" class="btn btn-vino"><i class="bi bi-plus-lg me-1"></i>Nuevo producto</a>
@endsection

@section('contenido')
<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="GET" role="search">
            <div class="col-12 col-lg-3">
                <label class="form-label small mb-1" for="buscar">Buscar</label>
                <input type="search" id="buscar" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" class="form-control" placeholder="Nombre del producto" maxlength="120">
            </div>
            <div class="col-6 col-lg-3">
                <label class="form-label small mb-1" for="categoria_id">Categoría</label>
                <select id="categoria_id" name="categoria_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach ($categorias as $c)<option value="{{ $c->id }}" @selected((string) ($filtros['categoria_id'] ?? '') === (string) $c->id)>{{ $c->nombre }}</option>@endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small mb-1" for="tipo">Tipo</label>
                <select id="tipo" name="tipo" class="form-select">
                    <option value="">Todos</option>
                    @foreach (\App\Models\Producto::TIPOS as $k => $t)<option value="{{ $k }}" @selected(($filtros['tipo'] ?? '') === $k)>{{ $t }}</option>@endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small mb-1" for="estado">Estado</label>
                <select id="estado" name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="activos" @selected(($filtros['estado'] ?? '') === 'activos')>Activos</option>
                    <option value="inactivos" @selected(($filtros['estado'] ?? '') === 'inactivos')>Inactivos</option>
                </select>
            </div>
            <div class="col-6 col-lg-2 d-flex gap-2">
                <button class="btn btn-vino flex-grow-1"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros" aria-label="Limpiar filtros"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th><span class="visually-hidden">Imagen</span></th><th>Producto</th><th>Categoría</th><th class="text-end">Precio</th><th class="text-end">Stock</th><th class="text-center">Carta</th><th class="text-center">Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            @forelse ($productos as $p)
                <tr class="{{ $p->activo ? '' : 'opacity-50' }}">
                    <td class="text-center" style="width:64px">
                        @if ($p->imagenUrl())<img src="{{ $p->imagenUrl() }}" class="miniatura" alt="" loading="lazy">
                        @else<span class="miniatura-vacia"><i class="bi {{ $p->esImplemento() ? 'bi-cup' : 'bi-cup-straw' }}"></i></span>@endif
                    </td>
                    <td>
                        <div class="fw-bold">{{ $p->nombre }}</div>
                        <small class="text-secondary">{{ $p->caracteristicas ?: \App\Models\Producto::TIPOS[$p->tipo] }}</small>
                    </td>
                    <td class="small">{{ $p->categoria->nombre }}</td>
                    <td class="text-end text-nowrap">{{ $p->esImplemento() ? '—' : 'S/ '.number_format($p->precio, 2) }}</td>
                    <td class="text-end fw-bold">{{ $p->stock_actual }}</td>
                    <td class="text-center">
                        @if ($p->visible_en_carta)
                            <i class="bi bi-eye text-success" title="Visible en la carta"></i><span class="visually-hidden">Visible en la carta</span>
                        @else
                            <i class="bi bi-eye-slash text-secondary" title="No se muestra en la carta"></i><span class="visually-hidden">No se muestra en la carta</span>
                        @endif
                    </td>
                    <td class="text-center"><span class="badge {{ $p->activo ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $p->activo ? 'Activo' : 'Inactivo' }}</span></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('kardex.show', $p) }}" class="btn btn-sm btn-outline-secondary btn-accion" title="Ver kardex" aria-label="Ver kardex de {{ $p->nombre }}"><i class="bi bi-clock-history"></i></a>
                        <a href="{{ route('productos.edit', $p) }}" class="btn btn-sm btn-outline-primary btn-accion"><i class="bi bi-pencil"></i> Editar</a>
                        <form method="POST" action="{{ route('productos.estado', $p) }}" class="d-inline"
                              data-confirmar="{{ $p->activo ? '¿Desactivar este producto? Dejará de mostrarse en la carta y su historial se conserva.' : '¿Reactivar este producto?' }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm btn-accion {{ $p->activo ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                    title="{{ $p->activo ? 'Desactivar' : 'Reactivar' }}" aria-label="{{ ($p->activo ? 'Desactivar ' : 'Reactivar ').$p->nombre }}">
                                <i class="bi {{ $p->activo ? 'bi-slash-circle' : 'bi-arrow-counterclockwise' }}"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-secondary py-5">No hay productos con esos filtros.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($productos->hasPages())<div class="card-footer">{{ $productos->links() }}</div>@endif
</div>
@endsection
