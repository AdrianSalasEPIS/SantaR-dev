<?php

namespace App\Exceptions;

use App\Models\Producto;

/** RN-07: no se puede sacar más que el stock disponible. */
class StockInsuficienteException extends ReglaNegocioException
{
    public function __construct(Producto $producto)
    {
        parent::__construct("Stock insuficiente de «{$producto->nombre}» (disponible: {$producto->stock_actual}).");
    }
}
