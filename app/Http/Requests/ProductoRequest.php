<?php

namespace App\Http\Requests;

use App\Models\Producto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * RF-08: productos e implementos. Los productos visibles en la carta llevan imagen obligatoria
 * (se registra junto con el producto); los implementos no se venden ni se muestran en la carta (RN-10).
 */
class ProductoRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $esImplemento = $this->input('tipo') === 'IMPLEMENTO';

        $this->merge([
            'nombre' => trim((string) $this->input('nombre')),
            'visible_en_carta' => ! $esImplemento && $this->boolean('visible_en_carta'),
            'precio' => $esImplemento ? 0 : $this->input('precio'),
        ]);
    }

    public function rules(): array
    {
        /** @var Producto|null $producto */
        $producto = $this->route('producto');
        $esProducto = $this->input('tipo') === 'PRODUCTO';
        $necesitaImagen = $esProducto && $this->boolean('visible_en_carta') && ! $producto?->imagen;

        return [
            'nombre' => ['required', 'string', 'max:120', Rule::unique('productos', 'nombre')->ignore($producto)],
            'tipo' => ['required', Rule::in(array_keys(Producto::TIPOS))],
            'categoria_id' => [
                'required', 'integer',
                // Una categoría desactivada solo se admite si es la que el producto ya tenía.
                Rule::exists('categorias', 'id')->where(fn ($q) => $q->where('activo', true)
                    ->when($producto, fn ($q) => $q->orWhere('id', $producto->categoria_id))),
            ],
            'caracteristicas' => ['nullable', 'string', 'max:100'],
            'precio' => $esProducto ? ['required', 'numeric', 'gt:0', 'max:99999.99', 'decimal:0,2'] : ['numeric', 'min:0'],
            'stock_minimo' => ['required', 'integer', 'min:0', 'max:100000'],
            // CU-07: el stock inicial solo se indica al registrar; después cambia únicamente con movimientos.
            'stock_inicial' => $producto ? ['prohibited'] : ['nullable', 'integer', 'min:0', 'max:100000'],
            'visible_en_carta' => ['boolean'],
            'imagen' => [$necesitaImagen ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048', 'dimensions:min_width=200,min_height=200,max_width=6000,max_height=6000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'categoria_id' => 'categoría',
            'caracteristicas' => 'característica',
            'stock_minimo' => 'stock mínimo',
            'stock_inicial' => 'stock inicial',
            'visible_en_carta' => 'mostrar en la carta',
        ];
    }

    public function messages(): array
    {
        return [
            'imagen.required' => 'Suba la imagen del producto: es la que verán los clientes en la carta digital.',
            'imagen.mimes' => 'La imagen debe ser JPG o PNG.',
            'imagen.max' => 'La imagen no debe superar los 2 MB.',
            'imagen.dimensions' => 'La imagen debe medir al menos 200 × 200 píxeles.',
            'precio.gt' => 'El precio debe ser mayor que cero.',
            'precio.decimal' => 'El precio admite como máximo dos decimales.',
            'stock_inicial.prohibited' => 'El stock se modifica con entradas, salidas o ajustes.',
        ];
    }
}
