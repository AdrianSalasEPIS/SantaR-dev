@extends('layouts.app')

@section('titulo', 'Registrar abastecimiento')
@section('subtitulo', 'Productos recibidos de la bodega principal. Si un producto se repite, sus cantidades se suman.')

@section('acciones')
    <a href="{{ route('abastecimientos.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
@endsection

@php
    $opciones = function ($seleccionado = null) use ($productos) {
        $html = '<option value="">— Seleccione —</option>';
        foreach ($productos as $categoria => $grupo) {
            $html .= '<optgroup label="'.e($categoria).'">';
            foreach ($grupo as $p) {
                $sel = (string) $seleccionado === (string) $p->id ? ' selected' : '';
                $html .= '<option value="'.$p->id.'"'.$sel.'>'.e($p->nombre.($p->caracteristicas ? ' · '.$p->caracteristicas : '')).' (stock '.$p->stock_actual.')</option>';
            }
            $html .= '</optgroup>';
        }
        return $html;
    };
    $filas = old('items', [['producto_id' => '', 'cantidad' => '']]);
@endphp

@section('contenido')
<form method="POST" data-un-envio action="{{ route('abastecimientos.store') }}" class="form-tactil">
    @csrf
    <div class="row g-4">
        <div class="col-12 col-xl-4">
            <div class="card">
                <div class="card-header"><i class="bi bi-receipt me-2 text-oro"></i>Datos de la recepción</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="fecha_recepcion" class="form-label">Fecha de recepción</label>
                        <input type="date" id="fecha_recepcion" name="fecha_recepcion" max="{{ today()->toDateString() }}"
                               value="{{ old('fecha_recepcion', today()->toDateString()) }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="nro_guia" class="form-label">N.° de guía <span class="text-secondary fw-normal">(opcional)</span></label>
                        <input type="text" id="nro_guia" name="nro_guia" maxlength="30" value="{{ old('nro_guia') }}" class="form-control" placeholder="Ej.: G-0158">
                    </div>
                    <div>
                        <label for="observacion" class="form-label">Observación <span class="text-secondary fw-normal">(opcional)</span></label>
                        <textarea id="observacion" name="observacion" rows="2" maxlength="255" class="form-control">{{ old('observacion') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-boxes me-2 text-oro"></i>Productos recibidos</span>
                    <button type="button" id="agregar-fila" class="btn btn-sm btn-oro"><i class="bi bi-plus-lg me-1"></i>Agregar producto</button>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th style="width:3rem">#</th><th>Producto</th><th style="width:9rem">Cantidad</th><th style="width:3.5rem"></th></tr></thead>
                        <tbody id="filas-abastecimiento">
                        @foreach ($filas as $i => $fila)
                            <tr>
                                <td class="numero-fila fw-bold text-secondary">{{ $loop->iteration }}</td>
                                <td><select name="items[{{ $i }}][producto_id]" class="form-select" required>{!! $opciones($fila['producto_id'] ?? null) !!}</select></td>
                                <td><input type="number" name="items[{{ $i }}][cantidad]" min="1" step="1" inputmode="numeric" value="{{ $fila['cantidad'] ?? '' }}" class="form-control text-center fw-bold" required></td>
                                <td><button type="button" class="btn btn-outline-danger quitar-fila" aria-label="Quitar fila"><i class="bi bi-trash"></i></button></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <button class="btn btn-vino btn-lg w-100"><i class="bi bi-check2-circle me-2"></i>Registrar abastecimiento</button>
                </div>
            </div>
        </div>
    </div>
</form>

<template id="plantilla-fila">
    <tr>
        <td class="numero-fila fw-bold text-secondary"></td>
        <td><select name="items[__INDICE__][producto_id]" class="form-select" required>{!! $opciones() !!}</select></td>
        <td><input type="number" name="items[__INDICE__][cantidad]" min="1" step="1" inputmode="numeric" class="form-control text-center fw-bold" required></td>
        <td><button type="button" class="btn btn-outline-danger quitar-fila" aria-label="Quitar fila"><i class="bi bi-trash"></i></button></td>
    </tr>
</template>
@endsection
