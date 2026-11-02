<?php

namespace App\Http\Requests;

use App\Services\InventarioService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** RF-09 · RN-08: cantidades enteras mayores que cero. */
class EntradaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'producto_id' => ['required', 'integer', Rule::exists('productos', 'id')->where('activo', true)],
            'motivo' => ['required', Rule::in(array_keys(InventarioService::MOTIVOS_ENTRADA))],
            'cantidad' => ['required', 'integer', 'min:1', 'max:100000'],
            'observacion' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return ['producto_id' => 'producto', 'observacion' => 'observación'];
    }
}
