@extends('layouts.app')

@section('titulo', 'Registrar incidencia')
@section('subtitulo', 'Pérdidas, roturas u otros eventos de productos o implementos. El stock se descuenta automáticamente.')

@section('acciones')
    <a href="{{ route('incidencias.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
@endsection

@section('contenido')
<div class="row">
    <div class="col-12 col-xl-7">
        <div class="card">
            <div class="card-body p-4 form-tactil">
                <form method="POST" data-un-envio action="{{ route('incidencias.store') }}">
                    @csrf
                    @include('partials.selector-producto', ['etiqueta' => 'Producto o implemento afectado'])

                    <label class="form-label">Tipo de incidencia</label>
                    <div class="row g-2 mb-3">
                        @foreach ($tipos as $clave => $texto)
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="tipo" id="tipo-{{ $clave }}" value="{{ $clave }}"
                                       @checked(old('tipo', 'ROTURA') === $clave) autocomplete="off">
                                <label class="btn btn-outline-danger w-100 py-3" for="tipo-{{ $clave }}">
                                    <i class="bi {{ ['PERDIDA' => 'bi-question-octagon', 'ROTURA' => 'bi-hammer', 'OTRO' => 'bi-three-dots'][$clave] }} d-block fs-4 mb-1"></i>{{ $texto }}
                                </label>
                            </div>
                        @endforeach
                    </div>

                    @include('partials.cantidad')

                    <div class="mb-4">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="2" maxlength="255" required
                                  class="form-control @error('descripcion') is-invalid @enderror"
                                  placeholder="Ej.: se cayó al servir; botella rota al descargar.">{{ old('descripcion') }}</textarea>
                    </div>

                    <button class="btn btn-vino btn-lg w-100"><i class="bi bi-exclamation-octagon me-2"></i>Registrar incidencia</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
