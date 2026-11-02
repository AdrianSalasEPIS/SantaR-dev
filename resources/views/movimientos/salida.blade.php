@extends('layouts.app')

@section('titulo', 'Registrar salida')
@section('subtitulo', 'Venta, merma o consumo interno. El stock se descuenta automáticamente.')

@section('contenido')
<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="card">
            <div class="card-body p-4 form-tactil">
                <form method="POST" data-un-envio action="{{ route('movimientos.salida.guardar') }}">
                    @csrf
                    @include('partials.selector-producto')

                    <label class="form-label">Motivo</label>
                    <div class="row g-2 mb-3">
                        @foreach ($motivos as $clave => $texto)
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="motivo" id="motivo-{{ $clave }}" value="{{ $clave }}"
                                       @checked(old('motivo', 'VENTA') === $clave) autocomplete="off">
                                <label class="btn btn-outline-primary w-100 py-3" for="motivo-{{ $clave }}">
                                    <i class="bi {{ ['VENTA' => 'bi-bag-check', 'MERMA' => 'bi-droplet-half', 'CONSUMO_INTERNO' => 'bi-cup-straw'][$clave] }} d-block fs-4 mb-1"></i>{{ $texto }}
                                </label>
                            </div>
                        @endforeach
                    </div>

                    @include('partials.cantidad')

                    <div class="mb-4">
                        <label for="observacion" class="form-label">Observación <span class="text-secondary fw-normal">(opcional)</span></label>
                        <input type="text" id="observacion" name="observacion" maxlength="255" value="{{ old('observacion') }}" class="form-control">
                    </div>

                    <button class="btn btn-vino btn-lg w-100"><i class="bi bi-check2-circle me-2"></i>Registrar salida</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card">
            <div class="card-header"><i class="bi bi-info-circle me-2 text-oro"></i>Recuerde</div>
            <div class="card-body small">
                <ul class="mb-0 ps-3">
                    <li class="mb-2">No se puede sacar más de lo que hay en stock (RN-07).</li>
                    <li class="mb-2">Los implementos (copas, vasos) no aparecen aquí: sus roturas o pérdidas se registran en <a href="{{ route('incidencias.create') }}">Incidencias</a>.</li>
                    <li class="mb-2">Las salidas no se editan ni se borran. Si hubo un error, el administrador registra un ajuste.</li>
                    <li>Si el producto llega a su stock mínimo, el sistema le avisará.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
