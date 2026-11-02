@extends('layouts.app')

@section('titulo', 'Registrar entrada')
@section('subtitulo', 'Ingresos distintos a los abastecimientos de la bodega principal: compra local, devolución u otro.')

@section('acciones')
    <a href="{{ route('abastecimientos.create') }}" class="btn btn-outline-secondary"><i class="bi bi-truck me-1"></i>¿Llegó de la bodega? Registrar abastecimiento</a>
@endsection

@section('contenido')
<div class="row">
    <div class="col-12 col-xl-7">
        <div class="card">
            <div class="card-body p-4 form-tactil">
                <form method="POST" data-un-envio action="{{ route('movimientos.entrada.guardar') }}">
                    @csrf
                    @include('partials.selector-producto', ['etiqueta' => 'Producto o implemento'])

                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo</label>
                        <select id="motivo" name="motivo" class="form-select" required>
                            @foreach ($motivos as $clave => $texto)
                                <option value="{{ $clave }}" @selected(old('motivo') === $clave)>{{ $texto }}</option>
                            @endforeach
                        </select>
                    </div>

                    @include('partials.cantidad')

                    <div class="mb-4">
                        <label for="observacion" class="form-label">Observación <span class="text-secondary fw-normal">(opcional)</span></label>
                        <input type="text" id="observacion" name="observacion" maxlength="255" value="{{ old('observacion') }}" class="form-control">
                    </div>

                    <button class="btn btn-vino btn-lg w-100"><i class="bi bi-plus-circle me-2"></i>Registrar entrada</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
