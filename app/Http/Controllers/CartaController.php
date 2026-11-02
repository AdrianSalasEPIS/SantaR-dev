<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Response;

/**
 * CU-16 Consultar carta digital (RF-20 a RF-23). Única página pública y de solo lectura (RNF-07).
 * Muestra los productos activos, de tipo producto y visibles (RN-14), agrupados bajo títulos por categoría
 * en una sola página (RF-22), con «Agotado» cuando no hay stock (RF-21) y sin datos internos (RN-15).
 */
class CartaController extends Controller
{
    public function index(): Response
    {
        $productos = Producto::visiblesEnCarta()
            ->whereHas('categoria', fn ($q) => $q->where('activo', true))
            ->orderBy('nombre')
            ->get(['id', 'categoria_id', 'nombre', 'caracteristicas', 'precio', 'imagen', 'stock_actual'])
            ->groupBy('categoria_id');

        $categorias = Categoria::activas()->whereIn('id', $productos->keys())->get(['id', 'nombre']);

        return response()
            ->view('carta.index', ['categorias' => $categorias, 'productos' => $productos])
            // La carta lee el inventario en cada visita: el navegador no debe mostrar una versión vieja.
            ->header('Cache-Control', 'no-cache, must-revalidate');
    }
}
