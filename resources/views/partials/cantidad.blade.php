<div class="mb-3">
    <label for="{{ $nombre ?? 'cantidad' }}" class="form-label">{{ $etiqueta ?? 'Cantidad' }}</label>
    <div class="input-group input-group-lg cantidad-control" data-cantidad>
        <button class="btn btn-outline-secondary" type="button" data-paso="-1" aria-label="Restar uno">−</button>
        <input type="number" id="{{ $nombre ?? 'cantidad' }}" name="{{ $nombre ?? 'cantidad' }}" min="{{ $min ?? 1 }}" step="1" inputmode="numeric"
               value="{{ old($nombre ?? 'cantidad', $valor ?? 1) }}" class="form-control @error($nombre ?? 'cantidad') is-invalid @enderror" required>
        <button class="btn btn-outline-secondary" type="button" data-paso="1" aria-label="Sumar uno">+</button>
    </div>
</div>
