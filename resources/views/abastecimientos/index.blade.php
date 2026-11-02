@extends('layouts.app')

@section('titulo', 'Abastecimientos')
@section('subtitulo', 'Recepciones de productos enviados por la bodega principal.')

@section('acciones')
    <a href="{{ route('abastecimientos.create') }}" class="btn btn-vino"><i class="bi bi-plus-lg me-1"></i>Registrar abastecimiento</a>
@endsection

@section('contenido')
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>N.°</th><th>Fecha de recepción</th><th>Guía</th><th class="text-end">Productos</th><th class="text-end">Unidades</th><th>Registró</th><th></th></tr></thead>
            <tbody>
            @forelse ($abastecimientos as $a)
                <tr>
                    <td class="fw-bold">#{{ $a->id }}</td>
                    <td>{{ $a->fecha_recepcion->format('d/m/Y') }}</td>
                    <td>{{ $a->nro_guia ?? '—' }}</td>
                    <td class="text-end">{{ $a->detalles->count() }}</td>
                    <td class="text-end fw-bold text-success">+{{ $a->totalUnidades() }}</td>
                    <td class="small text-secondary">{{ $a->usuario->nombre }}</td>
                    <td class="text-end"><a href="{{ route('abastecimientos.show', $a) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye me-1"></i>Ver</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-secondary py-5">Aún no hay abastecimientos registrados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($abastecimientos->hasPages())<div class="card-footer">{{ $abastecimientos->links() }}</div>@endif
</div>
@endsection
