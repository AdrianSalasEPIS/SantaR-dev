<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** CU-04 Cambiar contraseña (RF-04, RN-04). */
class PerfilController extends Controller
{
    public function edit(Request $request): View
    {
        return view('perfil.edit', ['usuario' => $request->user()->load('rol')]);
    }

    public function actualizarContrasena(Request $request): RedirectResponse
    {
        $request->validate([
            'password_actual' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:password_actual', Password::defaults()],
        ], [
            'password_actual.current_password' => 'La contraseña actual no es correcta.',
            'password.different' => 'La nueva contraseña debe ser distinta de la actual.',
        ], [
            'password_actual' => 'contraseña actual',
            'password' => 'nueva contraseña',
        ]);

        $usuario = $request->user();
        $usuario->update(['password' => $request->input('password')]);

        // Cierra las demás sesiones abiertas con la contraseña anterior (por ejemplo, en el celular).
        DB::table(config('session.table', 'sessions'))
            ->where('user_id', $usuario->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();
        $request->session()->regenerate();

        Bitacora::registrar('seguridad', 'cambio de contraseña');

        return back()->with('exito', 'Su contraseña se actualizó correctamente. Las demás sesiones abiertas se cerraron.');
    }
}
