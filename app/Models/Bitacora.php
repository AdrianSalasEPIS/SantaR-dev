<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use LogicException;

/** RF-05 / RN-20: bitácora automática. Sus registros no se modifican ni se eliminan. */
class Bitacora extends Model
{
    public const MODULOS = ['seguridad' => 'Seguridad', 'usuarios' => 'Usuarios', 'catalogo' => 'Catálogo', 'inventario' => 'Inventario', 'sistema' => 'Sistema'];

    protected $table = 'bitacora';

    public $timestamps = false;

    protected $fillable = ['usuario_id', 'modulo', 'accion', 'detalle', 'ip', 'fecha_hora'];

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Los registros de la bitácora no se modifican.'));
        static::deleting(fn () => throw new LogicException('Los registros de la bitácora no se eliminan.'));
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    /** Registra quién hizo qué, desde dónde y cuándo (hora del servidor, RN-19). */
    public static function registrar(string $modulo, string $accion, ?string $detalle = null, ?int $usuarioId = null): void
    {
        static::create([
            'usuario_id' => $usuarioId ?? auth()->id(),
            'modulo' => $modulo,
            'accion' => Str::limit($accion, 60, ''),
            'detalle' => $detalle,
            'ip' => request()?->ip(),
            'fecha_hora' => now(),
        ]);
    }
}
