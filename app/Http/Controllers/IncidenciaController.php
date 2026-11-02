<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncidenciaRequest;
use App\Models\Incidencia;
use App\Models\Producto;
use App\Services\InventarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** CU-12 Registrar incidencia: pérdidas, roturas u otros eventos (RF-13). */
class IncidenciaController extends Controller
{
    public function index(): View
    {
        return view('incidencias.index', [
            'incidencias' => Incidencia::with(['producto', 'usuario'])->orderByDesc('fecha_hora')->orderByDesc('id')->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('incidencias.create', [
            'productos' => Producto::with('categoria')->activos()->orderBy('nombre')->get()
                ->groupBy(fn (Producto $p) => $p->categoria->nombre),
            'tipos' => Incidencia::TIPOS,
        ]);
    }

    public function store(IncidenciaRequest $request, InventarioService $inventario): RedirectResponse
    {
        $incidencia = $inventario->registrarIncidencia(
            (int) $request->producto_id, $request->tipo, (int) $request->cantidad, $request->descripcion
        );
        $producto = $incidencia->producto->refresh();

        $redireccion = redirect()->route('incidencias.index')->with('exito',
            'Incidencia registrada ('.Incidencia::TIPOS[$incidencia->tipo]."): −{$incidencia->cantidad} de «{$producto->nombre}». Stock actual: {$producto->stock_actual}.");

        // RF-16 / RN-11
        if ($inventario->verificarStockMinimo($producto)) {
            $redireccion->with('alerta', $producto->estaAgotado()
                ? "«{$producto->nombre}» quedó AGOTADO."
                : "«{$producto->nombre}» quedó en {$producto->stock_actual} (mínimo {$producto->stock_minimo}).");
        }

        return $redireccion;
    }
}
