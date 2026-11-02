<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $fillable = ['nombre', 'descripcion', 'orden_carta', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean', 'orden_carta' => 'integer'];
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function scopeActivas(Builder $q): Builder
    {
        return $q->where('activo', true)->orderBy('orden_carta')->orderBy('nombre');
    }
}
