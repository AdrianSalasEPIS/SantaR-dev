@extends('layouts.app')

@section('titulo', 'Abastecimiento #'.$abastecimiento->id)
@section('subtitulo', 'Recibido el '.$abastecimiento->fecha_recepcion->format('d/m/Y').' · registrado por '.$abastecimiento->usuario->nombre)

@section('acciones')
    <a href="{{ route('abastecimientos.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    <a href="{{ route('abastecimientos.create') }}" class="btn btn-vino"><i class="bi bi-plus-lg me-1"></i>Nuevo abastecimiento</a>
@endsection

@section('contenido')
<div class="row g-4">
    <div class="col-12 col-xl-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-receipt me-2 text-oro"></i>Resumen</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between"><span>N.° de guía</span><strong>{{ $abastecimiento->nro_guia ?? '—' }}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span>Productos</span><strong>{{ $abastecimiento->detalles->count() }}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span>Unidades recibidas</span><strong class="text-success">+{{ $abastecimiento->totalUnidades() }}</strong></li>
                @if ($abastecimiento->observacion)
                    <li class="list-group-item small">{{ $abastecimiento->observacion }}</li>
                @endif
            </ul>
        </div>
    </div>
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Producto</th><th>Categoría</th><th class="text-end">Cantidad</th><th class="text-end">Stock actual</th></tr></thead>
                    <tbody>
                    @foreach ($abastecimiento->detalles as $d)
                        <tr>
                            <td class="fw-semibold">{{ $d->producto->nombre }}</td>
                            <td class="small text-secondary">{{ $d->producto->categoria->nombre }}</td>
                            <td class="text-end fw-bold text-success">+{{ $d->cantidad }}</td>
                            <td class="text-end">{{ $d->producto->stock_actual }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
