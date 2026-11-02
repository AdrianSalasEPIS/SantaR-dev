<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/** RF-08: productos de venta e implementos del local (copas, vasos y similares). */
class Producto extends Model
{
    public const TIPOS = ['PRODUCTO' => 'Producto', 'IMPLEMENTO' => 'Implemento'];

    protected $table = 'productos';

    /** stock_actual no es asignable: solo lo cambia el servicio de inventario con movimientos (RF-14). */
    protected $fillable = [
        'categoria_id', 'nombre', 'tipo', 'caracteristicas', 'precio', 'imagen',
        'stock_minimo', 'visible_en_carta', 'activo',
    ];

    protected $attributes = [
        'stock_actual' => 0,
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'stock_actual' => 'integer',
            'stock_minimo' => 'integer',
            'visible_en_carta' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }

    public function scopeActivos(Builder $q): Builder
    {
        return $q->where('activo', true);
    }

    /** RN-11: alerta cuando el stock es igual o menor al mínimo. */
    public function scopeEnAlerta(Builder $q): Builder
    {
        return $q->whereColumn('stock_actual', '<=', 'stock_minimo');
    }

    public function estaAgotado(): bool
    {
        return $this->stock_actual === 0;
    }

    public function bajoStockMinimo(): bool
    {
        return $this->stock_actual <= $this->stock_minimo;
    }

    /** Estado para existencias y alertas (RF-15, RF-16). */
    public function estado(): string
    {
        return match (true) {
            $this->estaAgotado() => 'Agotado',
            $this->bajoStockMinimo() => 'Alerta',
            default => 'Normal',
        };
    }

    /** Productos que puede mostrar la carta digital (RN-14). */
    public function scopeVisiblesEnCarta(Builder $q): Builder
    {
        return $q->where('activo', true)->where('tipo', 'PRODUCTO')->where('visible_en_carta', true);
    }

    public function esImplemento(): bool
    {
        return $this->tipo === 'IMPLEMENTO';
    }

    public function imagenUrl(): ?string
    {
        return $this->imagen ? Storage::disk('public')->url($this->imagen) : null;
    }
}
