<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

/**
 * Cuenta única del encargado de la sede, con rol Administrador (RN-01).
 * Los datos se toman de ADMIN_NOMBRE, ADMIN_CORREO y ADMIN_PASSWORD del archivo .env.
 */
class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $admin = config('app.admin');

        $validacion = Validator::make($admin, [
            'correo' => ['required', 'email'],
            'password' => ['required', Password::defaults()],
        ]);
        if ($validacion->fails()) {
            throw new RuntimeException('Revise ADMIN_CORREO y ADMIN_PASSWORD en el archivo .env (contraseña de al menos 8 caracteres con letras y números): '
                .implode(' ', $validacion->errors()->all()));
        }

        Usuario::firstOrCreate(['correo' => mb_strtolower($admin['correo'])], [
            'rol_id' => Rol::where('nombre', Rol::ADMINISTRADOR)->value('id'),
            'nombre' => $admin['nombre'] ?: 'Encargado de sede',
            'password' => $admin['password'],
            'activo' => true,
        ]);
    }
}
