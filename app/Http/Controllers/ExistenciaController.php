<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** CU-13 Consultar existencias y alertas (RF-15, RF-16): búsqueda y filtros por categoría, tipo y estado. */
class ExistenciaController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:120'],
            'categoria_id' => ['nullable', 'integer'],
            'tipo' => ['nullable', 'in:PRODUCTO,IMPLEMENTO'],
            'estado' => ['nullable', 'in:alerta,agotado,normal'],
        ]);

        $productos = Producto::with('categoria')->activos()
            ->when($filtros['buscar'] ?? null, fn ($q, $v) => $q->where('nombre', 'like', '%'.addcslashes($v, '%_\\').'%'))
            ->when($filtros['categoria_id'] ?? null, fn ($q, $v) => $q->where('categoria_id', $v))
            ->when($filtros['tipo'] ?? null, fn ($q, $v) => $q->where('tipo', $v))
            ->when(($filtros['estado'] ?? null) === 'agotado', fn ($q) => $q->where('stock_actual', 0))
            ->when(($filtros['estado'] ?? null) === 'alerta', fn ($q) => $q->enAlerta()->where('stock_actual', '>', 0))
            ->when(($filtros['estado'] ?? null) === 'normal', fn ($q) => $q->whereColumn('stock_actual', '>', 'stock_minimo'))
            // Primero los agotados, luego los que están en alerta (RF-16).
            ->orderByRaw('stock_actual = 0 desc')
            ->orderByRaw('stock_actual <= stock_minimo desc')
            ->orderBy('nombre')
            ->paginate(15)->withQueryString();

        return view('existencias.index', [
            'productos' => $productos,
            'categorias' => Categoria::orderBy('orden_carta')->orderBy('nombre')->get(),
            'filtros' => $filtros,
            'resumen' => [
                'total' => Producto::activos()->count(),
                'alerta' => Producto::activos()->enAlerta()->where('stock_actual', '>', 0)->count(),
                'agotados' => Producto::activos()->where('stock_actual', 0)->count(),
            ],
        ]);
    }
}
