@php($admin = auth()->user()->esAdministrador())
<nav class="nav flex-column pb-4" aria-label="Opciones del sistema">
    <div class="seccion-menu">General</div>
    <a class="nav-link {{ request()->routeIs('panel') ? 'active' : '' }}" href="{{ route('panel') }}" @if (request()->routeIs('panel')) aria-current="page" @endif>
        <i class="bi bi-house-door"></i> Panel principal
    </a>

    @if ($admin)
        <div class="seccion-menu">Catálogo</div>
        <a class="nav-link {{ request()->routeIs('productos.*') ? 'active' : '' }}" href="{{ route('productos.index') }}" @if (request()->routeIs('productos.*')) aria-current="page" @endif>
            <i class="bi bi-cup-straw"></i> Productos
        </a>
        <a class="nav-link {{ request()->routeIs('categorias.*') ? 'active' : '' }}" href="{{ route('categorias.index') }}" @if (request()->routeIs('categorias.*')) aria-current="page" @endif>
            <i class="bi bi-tags"></i> Categorías
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
