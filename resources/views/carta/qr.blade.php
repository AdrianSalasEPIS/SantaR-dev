@extends('layouts.app')

@section('titulo', 'Carta digital')
@section('subtitulo', 'Código QR único de la carta. Imprima copias del mismo QR para las mesas: todas abren la misma página.')

@section('acciones')
    <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-up-right me-1"></i>Ver la carta</a>
@endsection

@section('contenido')
<div class="row g-4">
    <div class="col-12 col-xl-5">
        <div class="card text-center">
            <div class="card-header"><i class="bi bi-qr-code me-2 text-oro"></i>Código QR de la carta</div>
            <div class="card-body p-4">
                <div class="qr-vista mx-auto mb-3">{!! $qr !!}</div>
                <p class="small text-secondary mb-1">Dirección que abre el QR:</p>
                <p class="fw-bold text-break mb-4"><a href="{{ $url }}" target="_blank" rel="noopener">{{ $url }}</a></p>
                <div class="d-grid gap-2">
                    <a href="{{ route('qr.hoja') }}" class="btn btn-vino btn-lg"><i class="bi bi-printer me-2"></i>Descargar hoja para mesas (PDF)</a>
                    <a href="{{ route('qr.descargar') }}" class="btn btn-oro"><i class="bi bi-download me-2"></i>Descargar QR (SVG, alta calidad)</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-7">
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-info-circle me-2 text-oro"></i>Cómo funciona</div>
            <div class="card-body">
                <ol class="mb-0 ps-3">
                    <li class="mb-2">Descargue la <strong>hoja para mesas</strong>: trae varias copias del mismo QR listas para imprimir y recortar.</li>
                    <li class="mb-2">Coloque las copias en los porta-QR de las mesas y en un lugar visible del local.</li>
                    <li class="mb-2">El cliente escanea el QR con la cámara de su celular y ve la carta sin iniciar sesión.</li>
                    <li>La carta se actualiza sola con los precios, productos e imágenes del inventario. <strong>No hace falta volver a imprimir el QR</strong> cuando cambian los productos o precios (RN-17).</li>
                </ol>
            </div>
        </div>
        @if (! str_starts_with($url, 'https://'))
            <div class="alert alert-warning">
                <strong>Atención:</strong> está usando el sistema desde <code>{{ parse_url($url, PHP_URL_HOST) }}</code>.
                El QR apunta a esa dirección y no funcionará en el celular de los clientes. Descárguelo desde la versión publicada del sistema.
            </div>
        @endif
    </div>
</div>
@endsection
