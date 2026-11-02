<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\Producto;
use Illuminate\View\View;

/** Panel principal (CU-13): alertas de stock mínimo y productos agotados (RF-16, RN-11). */
class PanelController extends Controller
{
    public function index(): View
    {
        return view('panel', [
            'resumen' => [
                'productos' => Producto::activos()->count(),
                'alerta' => Producto::activos()->enAlerta()->where('stock_actual', '>', 0)->count(),
                'agotados' => Producto::activos()->where('stock_actual', 0)->count(),
                'movimientosHoy' => Movimiento::whereDate('fecha_hora', today())->count(),
            ],
            'alertas' => Producto::with('categoria')->activos()->enAlerta()
                ->orderBy('stock_actual')->orderBy('nombre')->limit(8)->get(),
            'ultimos' => Movimiento::with('producto')->orderByDesc('fecha_hora')->orderByDesc('id')->limit(8)->get(),
        ]);
    }
}
