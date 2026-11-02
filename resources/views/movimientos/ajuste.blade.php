@extends('layouts.app')

@section('titulo', 'Ajuste de inventario')
@section('subtitulo', 'Corrige el stock del sistema según el conteo físico. Queda registrado con su motivo en el kardex y la bitácora.')

@section('contenido')
<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="card">
            <div class="card-body p-4 form-tactil">
                <form method="POST" data-un-envio action="{{ route('movimientos.ajuste.guardar') }}">
                    @csrf
                    @include('partials.selector-producto', ['etiqueta' => 'Producto o implemento'])

                    @include('partials.cantidad', ['nombre' => 'stock_contado', 'etiqueta' => 'Stock contado físicamente', 'min' => 0, 'valor' => 0])

                    <div class="mb-4">
                        <label for="motivo" class="form-label">Motivo del ajuste</label>
                        <textarea id="motivo" name="motivo" rows="2" maxlength="255" class="form-control @error('motivo') is-invalid @enderror"
                                  placeholder="Ej.: conteo de cierre de semana; diferencia por error de registro." required>{{ old('motivo') }}</textarea>
                    </div>

                    <button class="btn btn-vino btn-lg w-100"><i class="bi bi-sliders me-2"></i>Registrar ajuste</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card">
            <div class="card-header"><i class="bi bi-shield-check me-2 text-oro"></i>¿Cuándo usar un ajuste?</div>
            <div class="card-body small">
                Los movimientos no se editan ni se eliminan (RN-09). Si el stock del sistema no coincide con el conteo
                físico, ingrese aquí la cantidad contada: el sistema calcula la diferencia y la registra como
                <strong>Ajuste (+)</strong> o <strong>Ajuste (−)</strong>. Solo el administrador puede hacerlo.
            </div>
        </div>
    </div>
</div>
@endsection
