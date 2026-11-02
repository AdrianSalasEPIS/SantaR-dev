<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** CU-01 Iniciar sesión (RF-01) y CU-02 Cerrar sesión (RF-02). No existe registro libre (RN-01). */
class LoginController extends Controller
{
    private const MAX_INTENTOS = 5;          // RN-05

    private const BLOQUEO_SEGUNDOS = 15 * 60; // RN-05

    public function mostrar(): View
    {
        return view('auth.login');
    }

    public function ingresar(Request $request): RedirectResponse
    {
        $request->merge(['correo' => Str::lower(trim((string) $request->input('correo')))]);
        $datos = $request->validate([
            'correo' => ['required', 'email', 'max:150'],
            'password' => ['required', 'string', 'max:255'],
        ], [], ['correo' => 'usuario', 'password' => 'contraseña']);

        $clave = $datos['correo'].'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($clave, self::MAX_INTENTOS)) {
            $minutos = (int) ceil(RateLimiter::availableIn($clave) / 60);

            return back()->onlyInput('correo')->withErrors([
                'correo' => "Demasiados intentos fallidos. Por seguridad, intente nuevamente en {$minutos} minuto(s).",
            ]);
        }

        if (! Auth::attempt(['correo' => $datos['correo'], 'password' => $datos['password'], 'activo' => true])) {
            RateLimiter::hit($clave, self::BLOQUEO_SEGUNDOS);

            $usuario = Usuario::where('correo', $datos['correo'])->first();
            if ($usuario && ! $usuario->activo && Hash::check($datos['password'], $usuario->password)) {
                return back()->onlyInput('correo')->withErrors(['correo' => 'Su cuenta no está habilitada. Comuníquese con el administrador.']);
            }

            Bitacora::registrar('seguridad', 'intento de ingreso fallido', $datos['correo'], $usuario?->id);

            return back()->onlyInput('correo')->withErrors(['correo' => 'Usuario o contraseña incorrectos.']);
        }

        RateLimiter::clear($clave);
        $request->session()->regenerate();
        $request->user()->forceFill(['ultimo_acceso' => now()])->save();
        Bitacora::registrar('seguridad', 'inicio de sesión');

        return redirect()->intended(route('panel'));
    }

    public function salir(Request $request): RedirectResponse
    {
        $porInactividad = $request->boolean('inactividad');
        Bitacora::registrar('seguridad', $porInactividad ? 'cierre por inactividad' : 'cierre de sesión');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $porInactividad
            ? redirect()->route('login')->with('aviso', 'Su sesión se cerró tras 15 minutos de inactividad.')
            : redirect()->route('login')->with('estado', 'Sesión cerrada correctamente.');
    }
}
