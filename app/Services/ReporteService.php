<?php

namespace App\Services;

use App\Models\Abastecimiento;
use App\Models\Categoria;
use App\Models\Incidencia;
use App\Models\Movimiento;
use App\Models\Producto;
use Illuminate\Support\Carbon;

/**
 * RF-18 (CU-15): arma los reportes de existencias, movimientos, abastecimientos e incidencias
 * con filtros por fechas y categoría. La misma estructura alimenta la pantalla, el PDF y el Excel (RF-19).
 */
class ReporteService
{
    public const TIPOS = [
        'existencias' => 'Existencias',
        'movimientos' => 'Movimientos',
        'abastecimientos' => 'Abastecimientos',
        'incidencias' => 'Incidencias',
    ];

    /**
     * @param  array{tipo:string, desde?:?string, hasta?:?string, categoria_id?:?int}  $filtros
     * @return array{tipo:string, titulo:string, periodo:string, categoria:?string, columnas:array<int,string>, filas:array<int,array<int,mixed>>, resumen:array<string,string|int>}
     */
    public function generar(array $filtros): array
    {
        $tipo = $filtros['tipo'];
        $desde = Carbon::parse($filtros['desde'] ?? now()->startOfMonth())->startOfDay();
        $hasta = Carbon::parse($filtros['hasta'] ?? now())->endOfDay();
        $categoriaId = filled($filtros['categoria_id'] ?? null) ? (int) $filtros['categoria_id'] : null;

        $datos = match ($tipo) {
            'existencias' => $this->existencias($categoriaId),
            'movimientos' => $this->movimientos($desde, $hasta, $categoriaId),
            'abastecimientos' => $this->abastecimientos($desde, $hasta, $categoriaId),
            'incidencias' => $this->incidencias($desde, $hasta, $categoriaId),
        };

        return $datos + [
            'tipo' => $tipo,
            'titulo' => 'Reporte de '.mb_strtolower(self::TIPOS[$tipo]),
            'periodo' => $tipo === 'existencias'
                ? 'Al '.now()->format('d/m/Y H:i')
                : 'Del '.$desde->format('d/m/Y').' al '.$hasta->format('d/m/Y'),
            'categoria' => $categoriaId ? Categoria::find($categoriaId)?->nombre : null,
        ];
    }

    private function existencias(?int $categoriaId): array
    {
        $productos = Producto::with('categoria')->activos()
            ->when($categoriaId, fn ($q, $v) => $q->where('categoria_id', $v))
            ->orderBy('nombre')->get()
            ->sortBy(fn (Producto $p) => [$p->categoria->orden_carta, $p->nombre]);

        return [
            'columnas' => ['Categoría', 'Producto', 'Tipo', 'Precio (S/)', 'Stock actual', 'Stock mínimo', 'Estado'],
            'filas' => $productos->map(fn (Producto $p) => [
                $p->categoria->nombre,
                $p->nombre.($p->caracteristicas ? ' · '.$p->caracteristicas : ''),
                Producto::TIPOS[$p->tipo],
                $p->esImplemento() ? '—' : number_format((float) $p->precio, 2),
                $p->stock_actual,
                $p->stock_minimo,
                $p->estado(),
            ])->values()->all(),
            'resumen' => [
                'Productos e implementos' => $productos->count(),
                'Unidades en stock' => $productos->sum('stock_actual'),
                'En stock mínimo' => $productos->filter(fn ($p) => $p->bajoStockMinimo() && ! $p->estaAgotado())->count(),
                'Agotados' => $productos->filter->estaAgotado()->count(),
            ],
        ];
    }

    private function movimientos(Carbon $desde, Carbon $hasta, ?int $categoriaId): array
    {
        $movimientos = Movimiento::with(['producto', 'usuario'])
            ->whereBetween('fecha_hora', [$desde, $hasta])
            ->when($categoriaId, fn ($q, $v) => $q->whereHas('producto', fn ($p) => $p->where('categoria_id', $v)))
            ->orderBy('fecha_hora')->orderBy('id')->get();

        return [
            'columnas' => ['Fecha y hora', 'Producto', 'Tipo', 'Motivo', 'Cantidad', 'Stock resultante', 'Usuario'],
            'filas' => $movimientos->map(fn (Movimiento $m) => [
                $m->fecha_hora->format('d/m/Y H:i'),
                $m->producto->nombre,
                Movimiento::TIPOS[$m->tipo],
                $m->motivoTexto(),
                ($m->suma() ? '+' : '−').$m->cantidad,
                $m->stock_resultante,
                $m->usuario->nombre,
            ])->all(),
            'resumen' => [
                'Movimientos' => $movimientos->count(),
                'Unidades ingresadas' => $movimientos->filter->suma()->sum('cantidad'),
                'Unidades retiradas' => $movimientos->reject->suma()->sum('cantidad'),
            ],
        ];
    }

    private function abastecimientos(Carbon $desde, Carbon $hasta, ?int $categoriaId): array
    {
        $abastecimientos = Abastecimiento::with(['usuario', 'detalles.producto'])
            ->whereBetween('fecha_recepcion', [$desde->toDateString(), $hasta->toDateString()])
            ->orderBy('fecha_recepcion')->orderBy('id')->get();

        $filas = [];
        foreach ($abastecimientos as $a) {
            foreach ($a->detalles as $d) {
                if ($categoriaId && $d->producto->categoria_id !== $categoriaId) {
                    continue;
                }
                $filas[] = [$a->fecha_recepcion->format('d/m/Y'), '#'.$a->id, $a->nro_guia ?? '—', $d->producto->nombre, '+'.$d->cantidad, $a->usuario->nombre];
            }
        }

        return [
            'columnas' => ['Fecha de recepción', 'N.°', 'Guía', 'Producto', 'Cantidad', 'Registró'],
            'filas' => $filas,
            'resumen' => [
                'Abastecimientos' => collect($filas)->pluck(1)->unique()->count(),
                'Unidades recibidas' => collect($filas)->sum(fn ($f) => (int) ltrim($f[4], '+')),
            ],
        ];
    }

    private function incidencias(Carbon $desde, Carbon $hasta, ?int $categoriaId): array
    {
        $incidencias = Incidencia::with(['producto', 'usuario'])
            ->whereBetween('fecha_hora', [$desde, $hasta])
            ->when($categoriaId, fn ($q, $v) => $q->whereHas('producto', fn ($p) => $p->where('categoria_id', $v)))
            ->orderBy('fecha_hora')->orderBy('id')->get();

        return [
            'columnas' => ['Fecha y hora', 'Producto', 'Tipo', 'Cantidad', 'Descripción', 'Registró'],
            'filas' => $incidencias->map(fn (Incidencia $i) => [
                $i->fecha_hora->format('d/m/Y H:i'),
                $i->producto->nombre,
                Incidencia::TIPOS[$i->tipo],
                '−'.$i->cantidad,
                $i->descripcion,
                $i->usuario->nombre,
            ])->all(),
            'resumen' => [
                'Incidencias' => $incidencias->count(),
                'Unidades afectadas' => $incidencias->sum('cantidad'),
                'Roturas' => $incidencias->where('tipo', 'ROTURA')->count(),
                'Pérdidas' => $incidencias->where('tipo', 'PERDIDA')->count(),
            ],
        ];
    }
}
