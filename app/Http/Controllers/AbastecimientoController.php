<?php

namespace App\Http\Controllers;

use App\Http\Requests\AbastecimientoRequest;
use App\Models\Abastecimiento;
use App\Models\Producto;
use App\Services\InventarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** CU-11 Registrar abastecimiento recibido de la bodega principal (RF-12). */
class AbastecimientoController extends Controller
{
    public function index(): View
    {
        return view('abastecimientos.index', [
            'abastecimientos' => Abastecimiento::with(['usuario', 'detalles'])
                ->orderByDesc('fecha_recepcion')->orderByDesc('id')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('abastecimientos.create', [
            'productos' => Producto::with('categoria')->activos()->orderBy('nombre')->get()
                ->groupBy(fn (Producto $p) => $p->categoria->nombre),
        ]);
    }

    public function store(AbastecimientoRequest $request, InventarioService $inventario): RedirectResponse
    {
        $abastecimiento = $inventario->registrarAbastecimiento(
            $request->fecha_recepcion, $request->nro_guia, $request->observacion, $request->validated('items')
        );

        return redirect()->route('abastecimientos.show', $abastecimiento)
            ->with('exito', 'Abastecimiento registrado: el stock de los productos recibidos se actualizó.');
    }

    public function show(Abastecimiento $abastecimiento): View
    {
        return view('abastecimientos.show', [
            'abastecimiento' => $abastecimiento->load(['usuario', 'detalles.producto.categoria']),
        ]);
    }
}
