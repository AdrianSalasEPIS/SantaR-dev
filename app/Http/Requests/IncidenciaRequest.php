<?php

namespace App\Http\Requests;

use App\Models\Incidencia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** RF-13 · RN-13: tipo, cantidad y descripción obligatorios. */
class IncidenciaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'producto_id' => ['required', 'integer', Rule::exists('productos', 'id')->where('activo', true)],
            'tipo' => ['required', Rule::in(array_keys(Incidencia::TIPOS))],
            'cantidad' => ['required', 'integer', 'min:1', 'max:100000'],
            'descripcion' => ['required', 'string', 'min:3', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return ['producto_id' => 'producto o implemento', 'descripcion' => 'descripción'];
    }
}
