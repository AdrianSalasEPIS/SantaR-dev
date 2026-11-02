<?php

namespace App\Http\Controllers;

use App\Exports\ReporteExport;
use App\Models\Bitacora;
use App\Models\Categoria;
use App\Services\ReporteService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

/** CU-15 Generar y exportar reportes (RF-18, RF-19). Solo el administrador. */
class ReporteController extends Controller
{
    /** Filas que se muestran en pantalla; la exportación incluye todas. */
    private const VISTA_PREVIA = 200;

    public function __construct(private readonly ReporteService $reportes) {}

    public function index(Request $request): View
    {
        $filtros = $this->filtros($request);

        return view('reportes.index', [
            'filtros' => $filtros,
            'reporte' => $this->reportes->generar($filtros),
            'categorias' => Categoria::orderBy('orden_carta')->orderBy('nombre')->get(),
            'tipos' => ReporteService::TIPOS,
            'limite' => self::VISTA_PREVIA,
        ]);
    }

    public function exportar(Request $request, string $formato): Response
    {
        $filtros = $this->filtros($request);
        $reporte = $this->reportes->generar($filtros);
        $archivo = 'santar-'.Str::slug($reporte['titulo']).'-'.now()->format('Ymd-His');

        Bitacora::registrar('reportes', 'reporte exportado', sprintf(
            '%s · %s · %s%s', $reporte['titulo'], strtoupper($formato), $reporte['periodo'],
            $reporte['categoria'] ? ' · '.$reporte['categoria'] : ''
        ));

        if ($formato === 'excel') {
            return Excel::download(new ReporteExport($reporte), $archivo.'.xlsx');
        }

        return Pdf::loadView('reportes.pdf', ['reporte' => $reporte])
            ->setPaper('a4', count($reporte['columnas']) > 6 ? 'landscape' : 'portrait')
            ->download($archivo.'.pdf');
    }

    private function filtros(Request $request): array
    {
        $datos = $request->validate([
            'tipo' => ['nullable', 'in:'.implode(',', array_keys(ReporteService::TIPOS))],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
            'categoria_id' => ['nullable', 'integer'],
        ], [], ['desde' => 'fecha inicial', 'hasta' => 'fecha final']);

        return [
            'tipo' => $datos['tipo'] ?? 'existencias',
            'desde' => $datos['desde'] ?? now()->startOfMonth()->toDateString(),
            'hasta' => $datos['hasta'] ?? now()->toDateString(),
            'categoria_id' => $datos['categoria_id'] ?? null,
        ];
    }
}
