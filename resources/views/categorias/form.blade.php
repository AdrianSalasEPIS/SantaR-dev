@extends('layouts.app')

@php($editando = $categoria->exists)
@section('titulo', $editando ? 'Editar categoría' : 'Nueva categoría')

@section('acciones')
    <a href="{{ route('categorias.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
@endsection

@section('contenido')
<div class="row">
    <div class="col-12 col-xl-7">
        <div class="card">
            <div class="card-body p-4 form-tactil">
                <form method="POST" data-un-envio action="{{ $editando ? route('categorias.update', $categoria) : route('categorias.store') }}">
                    @csrf
                    @if ($editando) @method('PUT') @endif
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" id="nombre" name="nombre" maxlength="80" value="{{ old('nombre', $categoria->nombre) }}"
                                   class="form-control @error('nombre') is-invalid @enderror" placeholder="Ej.: Vinos" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="orden_carta" class="form-label">Orden en la carta</label>
                            <input type="number" id="orden_carta" name="orden_carta" min="1" max="999" value="{{ old('orden_carta', $categoria->orden_carta) }}"
                                   class="form-control @error('orden_carta') is-invalid @enderror" required>
                        </div>
                        <div class="col-12">
                            <label for="descripcion" class="form-label">Descripción <span class="text-secondary fw-normal">(opcional)</span></label>
                            <input type="text" id="descripcion" name="descripcion" maxlength="255" value="{{ old('descripcion', $categoria->descripcion) }}" class="form-control">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-vino btn-lg px-5"><i class="bi bi-save me-2"></i>{{ $editando ? 'Guardar cambios' : 'Registrar categoría' }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
