@foreach ([
    'exito' => ['success', 'bi-check-circle-fill'],
    'alerta' => ['warning', 'bi-exclamation-triangle-fill'],
    'error' => ['danger', 'bi-x-octagon-fill'],
    'info' => ['info', 'bi-info-circle-fill'],
] as $clave => [$tipo, $icono])
    @if (session($clave))
        <div class="alert alert-{{ $tipo }} alert-dismissible fade show d-flex align-items-start gap-2 shadow-sm" role="alert">
            <i class="bi {{ $icono }} fs-5"></i>
            <div>{{ session($clave) }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif
@endforeach

@if ($errors->any())
    <div class="alert alert-danger shadow-sm" role="alert">
        <div class="d-flex gap-2 fw-bold mb-1"><i class="bi bi-x-octagon-fill"></i> Revise los datos ingresados:</div>
        <ul class="mb-0 ps-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
