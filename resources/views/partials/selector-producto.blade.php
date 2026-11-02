{{-- Lista de productos agrupada por categoría, con buscador y stock disponible (RNF-01). --}}
@php($idSelect = $id ?? 'producto_id')
<div class="mb-3">
    <label for="{{ $idSelect }}" class="form-label">{{ $etiqueta ?? 'Producto' }}</label>
    <div class="input-group mb-2">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="search" class="form-control" placeholder="Escriba para buscar…" data-filtro-de="{{ $idSelect }}" aria-label="Buscar producto">
    </div>
    <select id="{{ $idSelect }}" name="producto_id" class="form-select @error('producto_id') is-invalid @enderror"
            data-muestra-stock="{{ $idSelect }}-stock" required>
        <option value="">— Seleccione —</option>
        @foreach ($productos as $categoria => $grupo)
            <optgroup label="{{ $categoria }}">
                @foreach ($grupo as $p)
                    <option value="{{ $p->id }}" data-stock="{{ $p->stock_actual }}" data-minimo="{{ $p->stock_minimo }}"
                        @selected((string) old('producto_id', $seleccionado ?? '') === (string) $p->id)>
                        {{ $p->nombre }}{{ $p->caracteristicas ? ' · '.$p->caracteristicas : '' }}{{ $p->esImplemento() ? ' (implemento)' : '' }}
                    </option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
    <div id="{{ $idSelect }}-stock" class="mt-2"></div>
</div>
