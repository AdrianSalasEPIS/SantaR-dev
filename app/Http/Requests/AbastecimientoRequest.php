<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** RF-12 · RN-08 y RN-12: al menos un producto y cantidades enteras mayores que cero. */
class AbastecimientoRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        // Ignora las filas que el usuario dejó completamente vacías.
        $items = collect($this->input('items', []))
            ->filter(fn ($i) => is_array($i) && (filled($i['producto_id'] ?? null) || filled($i['cantidad'] ?? null)))
            ->values()
            ->all();

        $this->merge(['items' => $items]);
    }

    public function rules(): array
    {
        return [
            'fecha_recepcion' => ['required', 'date', 'before_or_equal:today', 'after_or_equal:'.now()->subYear()->toDateString()],
            'nro_guia' => ['nullable', 'string', 'max:30'],
            'observacion' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.producto_id' => ['required', 'integer', Rule::exists('productos', 'id')->where('activo', true)],
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:100000'],
        ];
    }

    public function attributes(): array
    {
        return ['fecha_recepcion' => 'fecha de recepción', 'nro_guia' => 'número de guía', 'observacion' => 'observación'];
    }

    public function messages(): array
    {
        return [
            'fecha_recepcion.before_or_equal' => 'La fecha de recepción no puede ser posterior a hoy.',
            'items.required' => 'Agregue al menos un producto al abastecimiento.',
            'items.min' => 'Agregue al menos un producto al abastecimiento.',
            'items.*.producto_id.required' => 'Seleccione el producto de la fila :position.',
            'items.*.producto_id.exists' => 'El producto de la fila :position no está disponible.',
            'items.*.cantidad.required' => 'Ingrese la cantidad de la fila :position.',
            'items.*.cantidad.min' => 'La cantidad de la fila :position debe ser mayor que cero.',
            'items.*.cantidad.integer' => 'La cantidad de la fila :position debe ser un número entero.',
        ];
    }
}
