<?php

namespace App\Models;

use App\Exceptions\ReglaNegocioException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** RF-14: cada entrada, salida, ajuste, abastecimiento o incidencia que modifica el stock (SAD, Figura 17). */
class Movimiento extends Model
{
    public const TIPOS = [
        'ENTRADA' => 'Entrada',
        'SALIDA' => 'Salida',
        'AJUSTE' => 'Ajuste',
        'ABASTECIMIENTO' => 'Abastecimiento',
        'INCIDENCIA' => 'Incidencia',
    ];

    public const MOTIVOS = [
        'COMPRA_LOCAL' => 'Compra local',
        'DEVOLUCION' => 'Devolución',
        'STOCK_INICIAL' => 'Stock inicial',
        'VENTA' => 'Venta',
        'MERMA' => 'Merma',
        'CONSUMO_INTERNO' => 'Consumo interno',
        'AJUSTE_POSITIVO' => 'Ajuste (+)',
        'AJUSTE_NEGATIVO' => 'Ajuste (−)',
        'BODEGA_PRINCIPAL' => 'Bodega principal',
        'PERDIDA' => 'Pérdida',
        'ROTURA' => 'Rotura',
        'OTRO' => 'Otro',
    ];

    protected $table = 'movimientos';

    public $timestamps = false;

    protected $fillable = [
        'producto_id', 'usuario_id', 'abastecimiento_id', 'incidencia_id', 'tipo', 'motivo',
        'cantidad', 'stock_resultante', 'observacion', 'fecha_hora',
    ];

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime', 'cantidad' => 'integer', 'stock_resultante' => 'integer'];
    }

    /** RN-09: los movimientos no se modifican ni se eliminan; se corrigen con un ajuste. */
    protected static function booted(): void
    {
        static::updating(fn () => throw new ReglaNegocioException('Los movimientos no se modifican; registre un ajuste de inventario.'));
        static::deleting(fn () => throw new ReglaNegocioException('Los movimientos no se eliminan; registre un ajuste de inventario.'));
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    /** Indica si el movimiento sumó stock (entrada, abastecimiento o ajuste positivo). */
    public function suma(): bool
    {
        return in_array($this->tipo, ['ENTRADA', 'ABASTECIMIENTO'], true)
            || $this->motivo === 'AJUSTE_POSITIVO';
    }

    public function motivoTexto(): string
    {
        return self::MOTIVOS[$this->motivo] ?? $this->motivo;
    }
}
