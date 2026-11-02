<?php

namespace App\Http\Requests;

use App\Services\InventarioService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** RF-10 · RN-08 y RN-10: solo productos (no implementos) y cantidades enteras mayores que cero. */
class SalidaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'producto_id' => ['required', 'integer',
                Rule::exists('productos', 'id')->where('activo', true)->where('tipo', 'PRODUCTO')],
            'motivo' => ['required', Rule::in(array_keys(InventarioService::MOTIVOS_SALIDA))],
            'cantidad' => ['required', 'integer', 'min:1', 'max:100000'],
            'observacion' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return ['producto_id' => 'producto', 'observacion' => 'observación'];
    }

    public function messages(): array
    {
        return ['producto_id.exists' => 'Seleccione un producto activo. Los implementos no tienen salidas: registre una incidencia.'];
    }
}
