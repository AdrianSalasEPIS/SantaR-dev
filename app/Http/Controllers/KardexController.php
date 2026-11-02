<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** CU-14 Consultar kardex (RF-17): historial de movimientos de un producto con el stock resultante. */
class KardexController extends Controller
{
    public function show(Request $request, Producto $producto): View
    {
        $filtros = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ], [], ['desde' => 'fecha inicial', 'hasta' => 'fecha final']);

        $movimientos = Movimiento::with('usuario')
            ->where('producto_id', $producto->id)
            ->when($filtros['desde'] ?? null, fn ($q, $v) => $q->whereDate('fecha_hora', '>=', $v))
            ->when($filtros['hasta'] ?? null, fn ($q, $v) => $q->whereDate('fecha_hora', '<=', $v))
            ->orderBy('fecha_hora')->orderBy('id')
            ->paginate(25)->withQueryString();

        return view('kardex.show', [
            'producto' => $producto->load('categoria'),
            'movimientos' => $movimientos,
            'filtros' => $filtros,
            'totales' => [
                'ingresos' => Movimiento::where('producto_id', $producto->id)
                    ->where(fn ($q) => $q->whereIn('tipo', ['ENTRADA', 'ABASTECIMIENTO'])->orWhere('motivo', 'AJUSTE_POSITIVO'))
                    ->sum('cantidad'),
                'egresos' => Movimiento::where('producto_id', $producto->id)
                    ->where(fn ($q) => $q->whereIn('tipo', ['SALIDA', 'INCIDENCIA'])->orWhere('motivo', 'AJUSTE_NEGATIVO'))
                    ->sum('cantidad'),
            ],
        ]);
    }
}
