@extends('layouts.app')

@section('titulo', 'Panel principal')
@php($hora = (int) now()->format('G'))
@section('subtitulo', ($hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches')).', '.auth()->user()->nombre.'. Este es el estado del inventario de la sede.')

@section('contenido')
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat vino"><div class="valor">{{ $resumen['productos'] }}</div><div class="etiqueta">Productos activos</div><i class="bi bi-box-seam fondo" aria-hidden="true"></i></div>
    </div>
    <div class="col-6 col-xl-3">
        <a href="{{ route('existencias.index', ['estado' => 'alerta']) }}" class="text-decoration-none">
            <div class="stat oro"><div class="valor">{{ $resumen['alerta'] }}</div><div class="etiqueta">En stock mínimo</div><i class="bi bi-exclamation-triangle fondo" aria-hidden="true"></i></div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="{{ route('existencias.index', ['estado' => 'agotado']) }}" class="text-decoration-none">
            <div class="stat rosado"><div class="valor">{{ $resumen['agotados'] }}</div><div class="etiqueta">Agotados</div><i class="bi bi-x-octagon fondo" aria-hidden="true"></i></div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat verde"><div class="valor">{{ $resumen['movimientosHoy'] }}</div><div class="etiqueta">Movimientos de hoy</div><i class="bi bi-arrow-left-right fondo" aria-hidden="true"></i></div>
    </div>
</div>

<h2 class="h5 text-vino mb-3"><i class="bi bi-lightning-charge me-1"></i>Accesos rápidos</h2>
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-xxl-3">
        <a href="{{ route('movimientos.salida') }}" class="acceso"><i class="bi bi-bag-check bg-vino"></i><span>Registrar venta / salida<small>Venta, merma o consumo interno</small></span></a>
    </div>
    <div class="col-12 col-md-6 col-xxl-3">
        <a href="{{ route('abastecimientos.create') }}" class="acceso"><i class="bi bi-truck bg-oro"></i><span>Recibir abastecimiento<small>Productos de la bodega principal</small></span></a>
    </div>
    <div class="col-12 col-md-6 col-xxl-3">
        <a href="{{ route('incidencias.create') }}" class="acceso"><i class="bi bi-exclamation-octagon bg-rosado"></i><span>Registrar incidencia<small>Pérdida, rotura u otro</small></span></a>
    </div>
    <div class="col-12 col-md-6 col-xxl-3">
        <a href="{{ route('movimientos.entrada') }}" class="acceso"><i class="bi bi-box-arrow-in-down bg-verde"></i><span>Registrar entrada<small>Compra local o devolución</small></span></a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xxl-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-bell me-2 text-oro"></i>Alertas de stock mínimo</span>
                <a href="{{ route('existencias.index', ['estado' => 'alerta']) }}" class="btn btn-sm btn-outline-secondary">Ver existencias</a>
            </div>
            <div class="card-body p-0">
                @forelse ($alertas as $p)
                    <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
                        @if ($p->imagenUrl())
                            <img src="{{ $p->imagenUrl() }}" class="miniatura" alt="" loading="lazy">
                        @else
                            <span class="miniatura-vacia"><i class="bi {{ $p->esImplemento() ? 'bi-cup' : 'bi-cup-straw' }}"></i></span>
                        @endif
                        <div class="flex-grow-1">
                            <div class="fw-bold">{{ $p->nombre }}</div>
                            <small class="text-secondary">{{ $p->categoria->nombre }} · mínimo {{ $p->stock_minimo }}</small>
                        </div>
                        <div class="text-end">
                            <div class="fs-5 fw-bold">{{ $p->stock_actual }}</div>
                            <span class="estado estado-{{ strtolower($p->estado()) }}">{{ $p->estado() }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-secondary py-5">
                        <i class="bi bi-emoji-smile fs-1 d-block mb-2 text-success"></i>Todo en orden: no hay productos en stock mínimo.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-12 col-xxl-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-clock-history me-2 text-oro"></i>Últimos movimientos</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Fecha</th><th>Producto</th><th>Movimiento</th><th class="text-end">Cant.</th><th class="text-end">Stock</th></tr></thead>
                        <tbody>
                        @forelse ($ultimos as $m)
                            <tr>
                                <td class="small text-secondary text-nowrap">{{ $m->fecha_hora->format('d/m H:i') }}</td>
                                <td class="fw-semibold">{{ $m->producto->nombre }}</td>
                                <td><span class="small">{{ \App\Models\Movimiento::TIPOS[$m->tipo] }}</span><br><small class="text-secondary">{{ $m->motivoTexto() }}</small></td>
                                <td class="text-end fw-bold {{ $m->suma() ? 'text-success' : 'text-danger' }}">{{ $m->suma() ? '+' : '−' }}{{ $m->cantidad }}</td>
                                <td class="text-end">{{ $m->stock_resultante }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-secondary py-5">Aún no hay movimientos registrados.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
