<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';

    protected $fillable = ['rol_id', 'nombre', 'correo', 'password', 'activo', 'ultimo_acceso'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'activo' => 'boolean',
            'ultimo_acceso' => 'datetime',
        ];
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class);
    }

    public function esAdministrador(): bool
    {
        return $this->rol?->nombre === Rol::ADMINISTRADOR;
    }

    public function tieneRol(string ...$roles): bool
    {
        return in_array($this->rol?->nombre, $roles, true);
    }

    /** Iniciales para el avatar del menú (ignora conectores: «Encargado de sede» → ES). */
    public function iniciales(): string
    {
        $conectores = ['de', 'del', 'la', 'las', 'los', 'y'];
        $partes = array_values(array_filter(
            preg_split('/\s+/', trim((string) $this->nombre)),
            fn ($p) => $p !== '' && ! in_array(mb_strtolower($p), $conectores, true)
        ));

        return mb_strtoupper(mb_substr($partes[0] ?? 'S', 0, 1).mb_substr($partes[1] ?? '', 0, 1));
    }
}
