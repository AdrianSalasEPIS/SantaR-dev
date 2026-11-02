@extends('layouts.app')

@section('titulo', 'Kardex · '.$producto->nombre)
@section('subtitulo', $producto->categoria->nombre.' · '.\App\Models\Producto::TIPOS[$producto->tipo].' · historial de movimientos con el stock resultante de cada uno.')

@section('acciones')
    <a href="{{ route('existencias.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver a existencias</a>
@endsection

@section('contenido')
<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="stat vino"><div class="valor">{{ $producto->stock_actual }}</div><div class="etiqueta">Stock actual</div><i class="bi bi-box-seam fondo" aria-hidden="true"></i></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat oro"><div class="valor">{{ $producto->stock_minimo }}</div><div class="etiqueta">Stock mínimo</div><i class="bi bi-exclamation-triangle fondo" aria-hidden="true"></i></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat verde"><div class="valor">+{{ $totales['ingresos'] }}</div><div class="etiqueta">Unidades ingresadas</div><i class="bi bi-box-arrow-in-down fondo" aria-hidden="true"></i></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat rosado"><div class="valor">−{{ $totales['egresos'] }}</div><div class="etiqueta">Unidades retiradas</div><i class="bi bi-box-arrow-up fondo" aria-hidden="true"></i></div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1" for="desde">Desde</label>
                <input type="date" id="desde" name="desde" value="{{ $filtros['desde'] ?? '' }}" class="form-control">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1" for="hasta">Hasta</label>
                <input type="date" id="hasta" name="hasta" value="{{ $filtros['hasta'] ?? '' }}" class="form-control">
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button class="btn btn-vino flex-grow-1"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="{{ route('kardex.show', $producto) }}" class="btn btn-outline-secondary" title="Limpiar filtros" aria-label="Limpiar filtros"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Fecha y hora</th><th>Movimiento</th><th>Motivo</th><th class="text-end">Entrada</th><th class="text-end">Salida</th><th class="text-end">Stock resultante</th><th>Usuario</th></tr></thead>
            <tbody>
            @forelse ($movimientos as $m)
                <tr>
                    <td class="text-nowrap small">{{ $m->fecha_hora->format('d/m/Y H:i') }}</td>
                    <td>{{ \App\Models\Movimiento::TIPOS[$m->tipo] }}</td>
                    <td class="small">
                        {{ $m->motivoTexto() }}
                        @if ($m->observacion)<div class="text-secondary">{{ $m->observacion }}</div>@endif
                    </td>
                    <td class="text-end fw-bold text-success">{{ $m->suma() ? '+'.$m->cantidad : '' }}</td>
                    <td class="text-end fw-bold text-danger">{{ $m->suma() ? '' : '−'.$m->cantidad }}</td>
                    <td class="text-end fw-bold">{{ $m->stock_resultante }}</td>
                    <td class="small text-secondary">{{ $m->usuario->nombre }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-secondary py-5">Este producto no tiene movimientos{{ ($filtros['desde'] ?? $filtros['hasta'] ?? null) ? ' en ese rango de fechas' : '' }}.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($movimientos->hasPages())<div class="card-footer">{{ $movimientos->links() }}</div>@endif
</div>
@endsection
