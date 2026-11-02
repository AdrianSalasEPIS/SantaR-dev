<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsuarioRequest;
use App\Models\Bitacora;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** CU-03 Gestionar usuarios (RF-03). Solo el administrador; las cuentas no se eliminan (RN-01, RN-03). */
class UsuarioController extends Controller
{
    public function index(): View
    {
        return view('usuarios.index', [
            'usuarios' => Usuario::with('rol')->orderByDesc('activo')->orderBy('nombre')->get(),
        ]);
    }

    public function create(): View
    {
        return view('usuarios.form', ['usuario' => new Usuario(['activo' => true]), 'roles' => Rol::orderBy('id')->get()]);
    }

    public function store(UsuarioRequest $request): RedirectResponse
    {
        $usuario = Usuario::create($request->validated() + ['activo' => true]);
        $usuario->load('rol');
        Bitacora::registrar('usuarios', 'usuario creado', "{$usuario->nombre} ({$usuario->correo}) · rol {$usuario->rol->nombre}");

        return redirect()->route('usuarios.index')->with('exito', "Cuenta de {$usuario->nombre} creada correctamente.");
    }

    public function edit(Usuario $usuario): View
    {
        return view('usuarios.form', ['usuario' => $usuario, 'roles' => Rol::orderBy('id')->get()]);
    }

    public function update(UsuarioRequest $request, Usuario $usuario): RedirectResponse
    {
        $datos = $request->validated();

        // El administrador no puede quitarse a sí mismo el rol: quedaría sin acceso a esta pantalla.
        if ($usuario->is($request->user()) && (int) $datos['rol_id'] !== $usuario->rol_id) {
            return back()->withInput()->withErrors(['rol_id' => 'No puede cambiar el rol de su propia cuenta.']);
        }
        $restablecida = filled($datos['password'] ?? null);
        if (! $restablecida) {
            unset($datos['password']);
        }

        $usuario->update($datos);

        // Si el administrador restablece la contraseña de otra persona, se cierran sus sesiones abiertas.
        if ($restablecida && ! $usuario->is($request->user())) {
            $this->cerrarSesiones($usuario);
        }

        Bitacora::registrar('usuarios', 'usuario editado', "{$usuario->nombre} ({$usuario->correo})"
            .($restablecida ? ' · contraseña restablecida' : ''));

        return redirect()->route('usuarios.index')->with('exito', "Datos de {$usuario->nombre} actualizados.");
    }

    public function cambiarEstado(Request $request, Usuario $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return back()->with('error', 'No puede desactivar su propia cuenta.');
        }

        $usuario->update(['activo' => ! $usuario->activo]);
        if (! $usuario->activo) {
            $this->cerrarSesiones($usuario);
        }
        Bitacora::registrar('usuarios', $usuario->activo ? 'usuario reactivado' : 'usuario desactivado', $usuario->correo);

        return back()->with('exito', $usuario->activo
            ? "La cuenta de {$usuario->nombre} fue reactivada."
            : "La cuenta de {$usuario->nombre} fue desactivada; su historial se conserva.");
    }

    private function cerrarSesiones(Usuario $usuario): void
    {
        DB::table(config('session.table', 'sessions'))->where('user_id', $usuario->id)->delete();
        $usuario->forceFill(['remember_token' => null])->save();
    }
}
