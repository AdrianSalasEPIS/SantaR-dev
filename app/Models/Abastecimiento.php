<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** RF-12: recepción de productos enviados por la bodega principal. */
class Abastecimiento extends Model
{
    protected $table = 'abastecimientos';

    protected $fillable = ['usuario_id', 'fecha_recepcion', 'nro_guia', 'observacion'];

    protected function casts(): array
    {
        return ['fecha_recepcion' => 'date'];
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleAbastecimiento::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function totalUnidades(): int
    {
        return (int) $this->detalles->sum('cantidad');
    }
}
