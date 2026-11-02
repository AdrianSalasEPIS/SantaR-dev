<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Panel principal: resumen del catálogo de la sede y actividad reciente. */
class PanelController extends Controller
{
    public function index(Request $request): View
    {
        $esAdmin = $request->user()->esAdministrador();

        return view('panel', [
            'resumen' => [
                'productos' => Producto::activos()->where('tipo', 'PRODUCTO')->count(),
                'implementos' => Producto::activos()->where('tipo', 'IMPLEMENTO')->count(),
                'enCarta' => Producto::visiblesEnCarta()->count(),
                'categorias' => Categoria::where('activo', true)->count(),
            ],
            'actividad' => $esAdmin
                ? Bitacora::with('usuario')->orderByDesc('fecha_hora')->orderByDesc('id')->limit(8)->get()
                : collect(),
        ]);
    }
}
