<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** RF-03 · RN-01 y RN-04: cuentas creadas solo por el administrador, contraseñas seguras. */
class UsuarioRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombre' => trim((string) $this->input('nombre')),
            'correo' => mb_strtolower(trim((string) $this->input('correo'))),
        ]);
    }

    public function rules(): array
    {
        $usuario = $this->route('usuario');

        return [
            'nombre' => ['required', 'string', 'min:3', 'max:100'],
            'correo' => ['required', 'email', 'max:150', Rule::unique('usuarios', 'correo')->ignore($usuario)],
            'rol_id' => ['required', 'integer', Rule::exists('roles', 'id')],
            'password' => [$usuario ? 'nullable' : 'required', 'confirmed', Password::defaults()],
        ];
    }

    public function attributes(): array
    {
        return ['correo' => 'correo electrónico', 'rol_id' => 'rol', 'password' => 'contraseña'];
    }
}
