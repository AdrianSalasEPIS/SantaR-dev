<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

/**
 * CU-17 Generar código QR de la carta (RF-24, RN-17). El QR apunta a la dirección fija de la carta:
 * no cambia cuando se modifican los productos o precios, y todas sus copias abren la misma página.
 * Se genera en SVG (vectorial: se imprime nítido a cualquier tamaño, sin depender de Imagick).
 */
class QrController extends Controller
{
    public function index(): View
    {
        return view('carta.qr', [
            'url' => route('carta'),
            'qr' => preg_replace('/^<\?xml[^>]*>\s*/', '', $this->svg(320)), // SVG incrustado en la página
        ]);
    }

    /** Descarga el QR en SVG de alta calidad para imprenta o diseño. */
    public function descargar(): Response
    {
        Bitacora::registrar('carta', 'código QR descargado', 'SVG · '.route('carta'));

        return new HttpResponse($this->svg(1200), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="santar-carta-qr.svg"',
        ]);
    }

    /** Hoja A4 lista para imprimir y recortar: copias del mismo QR para colocar en las mesas. */
    public function hoja(): Response
    {
        Bitacora::registrar('carta', 'hoja de QR descargada', 'PDF · '.route('carta'));

        return Pdf::loadView('carta.hoja-qr', [
            'url' => route('carta'),
            'qr' => 'data:image/svg+xml;base64,'.base64_encode($this->svg(600)),
        ])->setPaper('a4')->download('santar-carta-qr-mesas.pdf');
    }

    private function svg(int $tamano): string
    {
        return (string) QrCode::format('svg')->size($tamano)->margin(1)
            ->errorCorrection('H')->color(43, 10, 20)->generate(route('carta'));
    }
}
