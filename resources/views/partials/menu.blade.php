@php($admin = auth()->user()->esAdministrador())
<nav class="nav flex-column pb-4" aria-label="Opciones del sistema">
    <div class="seccion-menu">General</div>
    <a class="nav-link {{ request()->routeIs('panel') ? 'active' : '' }}" href="{{ route('panel') }}" @if (request()->routeIs('panel')) aria-current="page" @endif>
        <i class="bi bi-house-door"></i> Panel principal
    </a>
    <a class="nav-link {{ request()->routeIs('existencias.*') ? 'active' : '' }}" href="{{ route('existencias.index') }}" @if (request()->routeIs('existencias.*')) aria-current="page" @endif>
        <i class="bi bi-box-seam"></i> Existencias
    </a>

    <div class="seccion-menu">Movimientos</div>
    <a class="nav-link {{ request()->routeIs('movimientos.salida*') ? 'active' : '' }}" href="{{ route('movimientos.salida') }}" @if (request()->routeIs('movimientos.salida*')) aria-current="page" @endif>
        <i class="bi bi-bag-check"></i> Registrar salida
    </a>
    <a class="nav-link {{ request()->routeIs('movimientos.entrada*') ? 'active' : '' }}" href="{{ route('movimientos.entrada') }}" @if (request()->routeIs('movimientos.entrada*')) aria-current="page" @endif>
        <i class="bi bi-box-arrow-in-down"></i> Registrar entrada
    </a>
    <a class="nav-link {{ request()->routeIs('abastecimientos.*') ? 'active' : '' }}" href="{{ route('abastecimientos.index') }}" @if (request()->routeIs('abastecimientos.*')) aria-current="page" @endif>
        <i class="bi bi-truck"></i> Abastecimientos
    </a>
    <a class="nav-link {{ request()->routeIs('incidencias.*') ? 'active' : '' }}" href="{{ route('incidencias.index') }}" @if (request()->routeIs('incidencias.*')) aria-current="page" @endif>
        <i class="bi bi-exclamation-octagon"></i> Incidencias
    </a>
    @if ($admin)
        <a class="nav-link {{ request()->routeIs('movimientos.ajuste*') ? 'active' : '' }}" href="{{ route('movimientos.ajuste') }}" @if (request()->routeIs('movimientos.ajuste*')) aria-current="page" @endif>
            <i class="bi bi-sliders"></i> Ajuste de inventario
        </a>
    @endif

    @if ($admin)
        <div class="seccion-menu">Catálogo</div>
        <a class="nav-link {{ request()->routeIs('productos.*') ? 'active' : '' }}" href="{{ route('productos.index') }}" @if (request()->routeIs('productos.*')) aria-current="page" @endif>
            <i class="bi bi-cup-straw"></i> Productos
        </a>
        <a class="nav-link {{ request()->routeIs('categorias.*') ? 'active' : '' }}" href="{{ route('categorias.index') }}" @if (request()->routeIs('categorias.*')) aria-current="page" @endif>
            <i class="bi bi-tags"></i> Categorías
        </a>

        <div class="seccion-menu">Reportes y carta</div>
        <a class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}" href="{{ route('reportes.index') }}" @if (request()->routeIs('reportes.*')) aria-current="page" @endif>
            <i class="bi bi-clipboard-data"></i> Reportes
        </a>
        <a class="nav-link {{ request()->routeIs('qr.*') ? 'active' : '' }}" href="{{ route('qr.index') }}" @if (request()->routeIs('qr.*')) aria-current="page" @endif>
            <i class="bi bi-qr-code"></i> Carta digital (QR)
        </a>

        <div class="seccion-menu">Seguridad</div>
        <a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}" @if (request()->routeIs('usuarios.*')) aria-current="page" @endif>
            <i class="bi bi-people"></i> Usuarios
        </a>
        <a class="nav-link {{ request()->routeIs('bitacora.*') ? 'active' : '' }}" href="{{ route('bitacora.index') }}" @if (request()->routeIs('bitacora.*')) aria-current="page" @endif>
            <i class="bi bi-journal-text"></i> Bitácora
        </a>
    @endif

    <div class="seccion-menu">Mi cuenta</div>
    <a class="nav-link {{ request()->routeIs('perfil.*') ? 'active' : '' }}" href="{{ route('perfil.edit') }}" @if (request()->routeIs('perfil.*')) aria-current="page" @endif>
        <i class="bi bi-person-gear"></i> Cambiar contraseña
    </a>
</nav>
