<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/** RF-19: exporta a Excel el mismo reporte que se ve en pantalla (ReporteService). */
class ReporteExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    private const FILA_ENCABEZADO = 5;

    public function __construct(private readonly array $reporte) {}

    public function array(): array
    {
        $r = $this->reporte;
        $filas = [
            ['SantaR · Santa Rita VIP «Fina Herencia»'],
            [$r['titulo']],
            [$r['periodo'].($r['categoria'] ? ' · Categoría: '.$r['categoria'] : '')],
            [],
            $r['columnas'],
            ...$r['filas'],
            [],
        ];
        foreach ($r['resumen'] as $etiqueta => $valor) {
            $filas[] = [$etiqueta, $valor];
        }
        $filas[] = [];
        $filas[] = ['Generado el '.now()->format('d/m/Y H:i').' por '.(auth()->user()?->nombre ?? 'SantaR')];

        return $filas;
    }

    public function title(): string
    {
        return mb_substr($this->reporte['titulo'], 0, 31);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $evento) {
                $hoja = $evento->sheet->getDelegate();
                $ultimaColumna = chr(ord('A') + count($this->reporte['columnas']) - 1);

                $hoja->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('6D1A2B');
                $hoja->getStyle('A2')->getFont()->setBold(true)->setSize(12);
                $hoja->getStyle('A3')->getFont()->setItalic(true);

                $encabezado = 'A'.self::FILA_ENCABEZADO.":{$ultimaColumna}".self::FILA_ENCABEZADO;
                $hoja->getStyle($encabezado)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
                $hoja->getStyle($encabezado)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('6D1A2B');
                $hoja->freezePane('A'.(self::FILA_ENCABEZADO + 1));
            },
        ];
    }
}
