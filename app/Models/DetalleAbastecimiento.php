<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** RF-12: producto y cantidad de un abastecimiento. */
class DetalleAbastecimiento extends Model
{
    protected $table = 'detalle_abastecimientos';

    public $timestamps = false;

    protected $fillable = ['abastecimiento_id', 'producto_id', 'cantidad'];

    protected function casts(): array
    {
        return ['cantidad' => 'integer'];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
