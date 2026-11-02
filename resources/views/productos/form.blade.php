@extends('layouts.app')

@php($editando = $producto->exists)
@section('titulo', $editando ? 'Editar producto' : 'Nuevo producto')
@section('subtitulo', 'La imagen se registra junto con el producto: es la que verán los clientes en la carta digital.')

@section('acciones')
    <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
@endsection

@section('contenido')
<form method="POST" data-un-envio enctype="multipart/form-data" class="form-tactil"
      action="{{ $editando ? route('productos.update', $producto) : route('productos.store') }}">
    @csrf
    @if ($editando) @method('PUT') @endif
    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-7">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" id="nombre" name="nombre" maxlength="120" value="{{ old('nombre', $producto->nombre) }}"
                                   class="form-control @error('nombre') is-invalid @enderror" placeholder="Ej.: Vino Borgoña" required>
                        </div>
                        <div class="col-12 col-md-5">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select id="tipo" name="tipo" class="form-select" required>
                                @foreach (\App\Models\Producto::TIPOS as $k => $t)
                                    <option value="{{ $k }}" @selected(old('tipo', $producto->tipo) === $k)>{{ $t }}{{ $k === 'IMPLEMENTO' ? ' (copas, vasos…)' : ' (se vende)' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-7">
                            <label for="caracteristicas" class="form-label">Característica corta <span class="text-secondary fw-normal">(opcional)</span></label>
                            <input type="text" id="caracteristicas" name="caracteristicas" maxlength="100" value="{{ old('caracteristicas', $producto->caracteristicas) }}"
                                   class="form-control @error('caracteristicas') is-invalid @enderror" placeholder="Ej.: Semiseco · 750 ml  |  Picante · 40 g">
                        </div>
                        <div class="col-12 col-md-5">
                            <label for="categoria_id" class="form-label">Categoría</label>
                            <select id="categoria_id" name="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror" required>
                                <option value="">— Seleccione —</option>
                                @foreach ($categorias as $c)
                                    <option value="{{ $c->id }}" @selected((string) old('categoria_id', $producto->categoria_id) === (string) $c->id)>{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-4" data-solo-producto>
                            <label for="precio" class="form-label">Precio de venta (S/)</label>
                            <input type="number" id="precio" name="precio" min="0" step="0.10" value="{{ old('precio', $producto->exists ? $producto->precio : '') }}"
                                   class="form-control @error('precio') is-invalid @enderror" placeholder="0.00">
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="stock_minimo" class="form-label">Stock mínimo</label>
                            <input type="number" id="stock_minimo" name="stock_minimo" min="0" step="1" value="{{ old('stock_minimo', $producto->stock_minimo) }}"
                                   class="form-control @error('stock_minimo') is-invalid @enderror" required>
                        </div>
                        @unless ($editando)
                            <div class="col-6 col-md-4">
                                <label for="stock_inicial" class="form-label">Stock inicial</label>
                                <input type="number" id="stock_inicial" name="stock_inicial" min="0" step="1" inputmode="numeric" value="{{ old('stock_inicial', 0) }}"
                                       class="form-control @error('stock_inicial') is-invalid @enderror">
                                <div class="form-text">Se registra como entrada «Stock inicial».</div>
                            </div>
                        @else
                            <div class="col-6 col-md-4">
                                <span class="form-label d-block">Stock actual</span>
                                <div class="form-control bg-light">{{ $producto->stock_actual }} <small class="text-secondary">(cambia solo con movimientos)</small></div>
                            </div>
                        @endunless
                        <div class="col-12" data-solo-producto>
                            <input type="hidden" name="visible_en_carta" value="0">
                            <div class="form-check form-switch fs-5">
                                <input class="form-check-input" type="checkbox" role="switch" id="visible_en_carta" name="visible_en_carta" value="1"
                                       @checked(old('visible_en_carta', $producto->visible_en_carta))>
                                <label class="form-check-label fs-6" for="visible_en_carta">Mostrar en la carta digital</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-image me-2 text-oro"></i>Imagen del producto</div>
                <div class="card-body text-center">
                    <img id="vista-previa" src="{{ $producto->imagenUrl() }}" alt="Vista previa" class="vista-previa-imagen mb-3 {{ $producto->imagenUrl() ? '' : 'd-none' }}">
                    <div id="sin-imagen" class="vista-previa-imagen mb-3 mx-auto d-flex align-items-center justify-content-center text-oro {{ $producto->imagenUrl() ? 'd-none' : '' }}">
                        <i class="bi bi-camera fs-1"></i>
                    </div>
                    <input type="file" id="imagen" name="imagen" accept="image/jpeg,image/png" class="form-control @error('imagen') is-invalid @enderror">
                    <div class="form-text">JPG o PNG, máximo 2 MB. Obligatoria para los productos que se muestran en la carta.</div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <button class="btn btn-vino btn-lg px-5"><i class="bi bi-save me-2"></i>{{ $editando ? 'Guardar cambios' : 'Registrar producto' }}</button>
        </div>
    </div>
</form>
@endsection
