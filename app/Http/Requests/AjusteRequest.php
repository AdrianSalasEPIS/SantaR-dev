<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** RF-11 · RN-09: el ajuste exige el conteo físico y un motivo. */
class AjusteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'producto_id' => ['required', 'integer', Rule::exists('productos', 'id')->where('activo', true)],
            'stock_contado' => ['required', 'integer', 'min:0', 'max:100000'],
            'motivo' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return ['producto_id' => 'producto', 'stock_contado' => 'stock contado', 'motivo' => 'motivo del ajuste'];
    }
}
