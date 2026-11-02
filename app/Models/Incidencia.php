<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** RF-13: pérdida, rotura u otro evento que afecta a un producto o implemento (RN-13). */
class Incidencia extends Model
{
    public const TIPOS = ['PERDIDA' => 'Pérdida', 'ROTURA' => 'Rotura', 'OTRO' => 'Otro'];

    protected $table = 'incidencias';

    public $timestamps = false;

    protected $fillable = ['producto_id', 'usuario_id', 'tipo', 'cantidad', 'descripcion', 'fecha_hora'];

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime', 'cantidad' => 'integer'];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }
}
