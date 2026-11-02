<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Exceptions\StockInsuficienteException;
use App\Models\Abastecimiento;
use App\Models\Bitacora;
use App\Models\Incidencia;
use App\Models\Movimiento;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

/**
 * RF-14: único punto donde cambia el stock (SAD, Figura 16). Aplica las reglas RN-06 a RN-13
 * dentro de transacciones y bloquea la fila del producto (lockForUpdate) para que dos envíos
 * simultáneos, por ejemplo un doble toque en el terminal POS, no dejen el stock en negativo.
 */
class InventarioService
{
    public const MOTIVOS_ENTRADA = ['COMPRA_LOCAL' => 'Compra local', 'DEVOLUCION' => 'Devolución', 'OTRO' => 'Otro'];

    public const MOTIVOS_SALIDA = ['VENTA' => 'Venta', 'MERMA' => 'Merma', 'CONSUMO_INTERNO' => 'Consumo interno'];

    /** RF-09: entradas distintas a los abastecimientos (compra local, devolución u otro). */
    public function registrarEntrada(int $productoId, string $motivo, int $cantidad, ?string $observacion = null): Movimiento
    {
        $this->validarCantidad($cantidad);

        return DB::transaction(fn () => $this->aplicarMovimiento($productoId, 'ENTRADA', $motivo, $cantidad, $observacion));
    }

    /** RF-10: salidas por venta, merma o consumo interno, solo de productos (RN-10). */
    public function registrarSalida(int $productoId, string $motivo, int $cantidad, ?string $observacion = null): Movimiento
    {
        $this->validarCantidad($cantidad);

        return DB::transaction(fn () => $this->aplicarMovimiento($productoId, 'SALIDA', $motivo, -$cantidad, $observacion, soloProductos: true));
    }

    /** RF-11: corrige el stock según el conteo físico; devuelve null si no hay diferencia. */
    public function registrarAjuste(int $productoId, int $stockContado, string $motivo): ?Movimiento
    {
        if ($stockContado < 0) {
            throw new ReglaNegocioException('El stock contado no puede ser negativo.', 'stock_contado');
        }

        return DB::transaction(function () use ($productoId, $stockContado, $motivo) {
            $actual = Producto::lockForUpdate()->findOrFail($productoId)->stock_actual;
            $delta = $stockContado - $actual;
            if ($delta === 0) {
                return null;
            }

            return $this->aplicarMovimiento($productoId, 'AJUSTE', $delta > 0 ? 'AJUSTE_POSITIVO' : 'AJUSTE_NEGATIVO', $delta, $motivo);
        });
    }

    /**
     * RF-12: recepción de productos de la bodega principal. Los productos repetidos suman sus
     * cantidades (RN-12) y todo se guarda en una sola transacción: si algo falla, no se guarda nada.
     *
     * @param  array<int, array{producto_id:int|string, cantidad:int|string}>  $items
     */
    public function registrarAbastecimiento(string $fechaRecepcion, ?string $nroGuia, ?string $observacion, array $items): Abastecimiento
    {
        $agrupados = collect($items)
            ->groupBy(fn ($i) => (int) $i['producto_id'])
            ->map(fn ($grupo) => (int) $grupo->sum(fn ($i) => (int) $i['cantidad']));

        if ($agrupados->isEmpty()) {
            throw new ReglaNegocioException('El abastecimiento debe tener al menos un producto.', 'items');
        }
        $agrupados->each(fn ($cantidad) => $this->validarCantidad($cantidad, 'items'));

        return DB::transaction(function () use ($fechaRecepcion, $nroGuia, $observacion, $agrupados) {
            $abastecimiento = Abastecimiento::create([
                'usuario_id' => auth()->id(),
                'fecha_recepcion' => $fechaRecepcion,
                'nro_guia' => $nroGuia,
                'observacion' => $observacion,
            ]);

            foreach ($agrupados as $productoId => $cantidad) {
                $abastecimiento->detalles()->create(['producto_id' => $productoId, 'cantidad' => $cantidad]);
                $this->aplicarMovimiento($productoId, 'ABASTECIMIENTO', 'BODEGA_PRINCIPAL', $cantidad, null,
                    abastecimientoId: $abastecimiento->id);
            }

            Bitacora::registrar('inventario', 'abastecimiento registrado', sprintf(
                'Abastecimiento #%d%s: %d producto(s), %d unidad(es)',
                $abastecimiento->id, $nroGuia ? " (guía {$nroGuia})" : '', $agrupados->count(), $agrupados->sum()
            ));

            return $abastecimiento;
        });
    }

    /** RF-13: pérdidas, roturas u otros eventos de productos o implementos (RN-07, RN-13). */
    public function registrarIncidencia(int $productoId, string $tipo, int $cantidad, string $descripcion): Incidencia
    {
        $this->validarCantidad($cantidad);
        if (! array_key_exists($tipo, Incidencia::TIPOS)) {
            throw new ReglaNegocioException('Tipo de incidencia no válido.', 'tipo');
        }

        return DB::transaction(function () use ($productoId, $tipo, $cantidad, $descripcion) {
            $producto = Producto::lockForUpdate()->findOrFail($productoId);
            if ($cantidad > $producto->stock_actual) {
                throw new StockInsuficienteException($producto);
            }

            $incidencia = Incidencia::create([
                'producto_id' => $productoId,
                'usuario_id' => auth()->id(),
                'tipo' => $tipo,
                'cantidad' => $cantidad,
                'descripcion' => $descripcion,
                'fecha_hora' => now(),
            ]);

            $this->aplicarMovimiento($productoId, 'INCIDENCIA', $tipo, -$cantidad, $descripcion, incidenciaId: $incidencia->id);

            return $incidencia;
        });
    }

    /** RF-08 + RF-14: el stock inicial de un producto nuevo se registra como una entrada (CU-07, paso 6). */
    public function registrarStockInicial(int $productoId, int $cantidad): ?Movimiento
    {
        if ($cantidad <= 0) {
            return null;
        }

        return DB::transaction(fn () => $this->aplicarMovimiento($productoId, 'ENTRADA', 'STOCK_INICIAL', $cantidad, 'Stock inicial al registrar el producto'));
    }

    /** RN-11: verifica si el producto quedó en su stock mínimo o por debajo. */
    public function verificarStockMinimo(Producto $producto): bool
    {
        return $producto->bajoStockMinimo();
    }

    /** RN-08: las cantidades de todos los movimientos son enteros mayores que cero. */
    private function validarCantidad(int $cantidad, string $campo = 'cantidad'): void
    {
        if ($cantidad <= 0) {
            throw new ReglaNegocioException('La cantidad debe ser un número entero mayor que cero.', $campo);
        }
    }

    /**
     * Bloquea el producto, valida el stock, lo actualiza, crea el movimiento y registra la bitácora.
     * Debe ejecutarse dentro de una transacción.
     */
    private function aplicarMovimiento(
        int $productoId,
        string $tipo,
        string $motivo,
        int $delta,
        ?string $observacion,
        bool $soloProductos = false,
        ?int $abastecimientoId = null,
        ?int $incidenciaId = null,
    ): Movimiento {
        $producto = Producto::lockForUpdate()->findOrFail($productoId);

        if (! $producto->activo) {
            throw new ReglaNegocioException("El producto «{$producto->nombre}» está desactivado.", 'producto_id');
        }
        if ($soloProductos && $producto->esImplemento()) {
            throw new ReglaNegocioException('Los implementos no tienen salidas; registre una incidencia (RN-10).', 'producto_id');
        }

        // RN-06 y RN-07: el stock resulta de sumar y restar movimientos, y nunca queda negativo.
        $nuevoStock = $producto->stock_actual + $delta;
        if ($nuevoStock < 0) {
            throw new StockInsuficienteException($producto);
        }

        $producto->stock_actual = $nuevoStock;
        $producto->save();

        $movimiento = Movimiento::create([
            'producto_id' => $producto->id,
            'usuario_id' => auth()->id(),
            'abastecimiento_id' => $abastecimientoId,
            'incidencia_id' => $incidenciaId,
            'tipo' => $tipo,
            'motivo' => $motivo,
            'cantidad' => abs($delta),
            'stock_resultante' => $nuevoStock,
            'observacion' => $observacion,
            'fecha_hora' => now(), // RN-19: hora del servidor (America/Lima)
        ]);

        $accion = [
            'ENTRADA' => 'entrada registrada',
            'SALIDA' => 'salida registrada',
            'AJUSTE' => 'ajuste registrado',
            'ABASTECIMIENTO' => 'ingreso por abastecimiento',
            'INCIDENCIA' => 'incidencia registrada',
        ][$tipo];

        Bitacora::registrar('inventario', $accion, sprintf(
            '%s · %s %s%d · stock resultante %d',
            $producto->nombre, Movimiento::MOTIVOS[$motivo] ?? $motivo, $delta >= 0 ? '+' : '−', abs($delta), $nuevoStock
        ));

        return $movimiento;
    }
}
