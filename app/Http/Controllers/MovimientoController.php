<?php

namespace App\Http\Controllers;

use App\Http\Requests\AjusteRequest;
use App\Http\Requests\EntradaRequest;
use App\Http\Requests\SalidaRequest;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Services\InventarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/** CU-08 Registrar entrada (RF-09), CU-09 Registrar salida (RF-10) y CU-10 Registrar ajuste (RF-11). */
class MovimientoController extends Controller
{
    public function __construct(private readonly InventarioService $inventario) {}

    public function crearEntrada(): View
    {
        return view('movimientos.entrada', [
            'productos' => $this->productos(),
            'motivos' => InventarioService::MOTIVOS_ENTRADA,
        ]);
    }

    public function guardarEntrada(EntradaRequest $request): RedirectResponse
    {
        $m = $this->inventario->registrarEntrada(
            (int) $request->producto_id, $request->motivo, (int) $request->cantidad, $request->observacion
        );

        return $this->resultado($m, "Entrada registrada: +{$m->cantidad} de «{$m->producto->nombre}».", 'movimientos.entrada');
    }

    public function crearSalida(): View
    {
        return view('movimientos.salida', [
            'productos' => $this->productos(soloProductos: true),
            'motivos' => InventarioService::MOTIVOS_SALIDA,
        ]);
    }

    public function guardarSalida(SalidaRequest $request): RedirectResponse
    {
        $m = $this->inventario->registrarSalida(
            (int) $request->producto_id, $request->motivo, (int) $request->cantidad, $request->observacion
        );

        return $this->resultado($m, "Salida registrada ({$m->motivoTexto()}): −{$m->cantidad} de «{$m->producto->nombre}».", 'movimientos.salida');
    }

    public function crearAjuste(): View
    {
        return view('movimientos.ajuste', ['productos' => $this->productos()]);
    }

    public function guardarAjuste(AjusteRequest $request): RedirectResponse
    {
        $m = $this->inventario->registrarAjuste((int) $request->producto_id, (int) $request->stock_contado, $request->motivo);

        if (! $m) {
            return back()->withInput()->with('info', 'El stock contado coincide con el registrado: no es necesario el ajuste.');
        }

        return $this->resultado($m, "Ajuste registrado: {$m->motivoTexto()} {$m->cantidad} en «{$m->producto->nombre}».", 'movimientos.ajuste');
    }

    /** Productos activos agrupados por categoría para las listas de selección (registro en menos de un minuto, RNF-01). */
    private function productos(bool $soloProductos = false): Collection
    {
        return Producto::with('categoria')->activos()
            ->when($soloProductos, fn ($q) => $q->where('tipo', 'PRODUCTO'))
            ->orderBy('nombre')->get()
            ->groupBy(fn (Producto $p) => $p->categoria->nombre);
    }

    private function resultado(Movimiento $m, string $mensaje, string $ruta): RedirectResponse
    {
        $producto = $m->producto->refresh();
        $redireccion = redirect()->route($ruta)->with('exito', "{$mensaje} Stock actual: {$producto->stock_actual}.");

        // RF-16 / RN-11: alerta cuando el stock queda igual o por debajo del mínimo.
        if ($this->inventario->verificarStockMinimo($producto)) {
            $redireccion->with('alerta', $producto->estaAgotado()
                ? "«{$producto->nombre}» quedó AGOTADO."
                : "«{$producto->nombre}» quedó en {$producto->stock_actual} unidades (mínimo {$producto->stock_minimo}). Conviene reponer.");
        }

        return $redireccion;
    }
}
