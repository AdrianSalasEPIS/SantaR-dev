@extends('layouts.app')

@section('titulo', 'Incidencias')
@section('subtitulo', 'Historial de pérdidas, roturas y otros eventos que afectaron el stock.')

@section('acciones')
    <a href="{{ route('incidencias.create') }}" class="btn btn-vino"><i class="bi bi-plus-lg me-1"></i>Registrar incidencia</a>
@endsection

@section('contenido')
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Fecha y hora</th><th>Producto</th><th>Tipo</th><th class="text-end">Cantidad</th><th>Descripción</th><th>Registró</th></tr></thead>
            <tbody>
            @forelse ($incidencias as $i)
                <tr>
                    <td class="text-nowrap small">{{ $i->fecha_hora->format('d/m/Y H:i') }}</td>
                    <td class="fw-semibold">{{ $i->producto->nombre }}</td>
                    <td><span class="estado estado-agotado">{{ \App\Models\Incidencia::TIPOS[$i->tipo] }}</span></td>
                    <td class="text-end fw-bold text-danger">−{{ $i->cantidad }}</td>
                    <td class="small">{{ $i->descripcion }}</td>
                    <td class="small text-secondary">{{ $i->usuario->nombre }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-secondary py-5">No hay incidencias registradas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($incidencias->hasPages())<div class="card-footer">{{ $incidencias->links() }}</div>@endif
</div>
@endsection
