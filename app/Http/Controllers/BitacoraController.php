<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** CU-05 Consultar bitácora (RF-06): filtros por usuario, módulo y rango de fechas. */
class BitacoraController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'usuario_id' => ['nullable', 'integer'],
            'modulo' => ['nullable', 'string', 'in:'.implode(',', array_keys(Bitacora::MODULOS))],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        $registros = Bitacora::with('usuario')
            ->when($filtros['usuario_id'] ?? null, fn ($q, $v) => $q->where('usuario_id', $v))
            ->when($filtros['modulo'] ?? null, fn ($q, $v) => $q->where('modulo', $v))
            ->when($filtros['desde'] ?? null, fn ($q, $v) => $q->whereDate('fecha_hora', '>=', $v))
            ->when($filtros['hasta'] ?? null, fn ($q, $v) => $q->whereDate('fecha_hora', '<=', $v))
            ->orderByDesc('fecha_hora')->orderByDesc('id')
            ->paginate(15)->withQueryString();

        return view('bitacora.index', [
            'registros' => $registros,
            'usuarios' => Usuario::orderBy('nombre')->get(['id', 'nombre']),
            'modulos' => Bitacora::MODULOS,
            'filtros' => $filtros,
        ]);
    }
}
