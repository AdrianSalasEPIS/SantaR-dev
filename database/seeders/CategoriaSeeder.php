<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

/** Categorías de referencia del rubro (las definitivas las confirmará el propietario). */
class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['Vinos', 'Vinos por botella.', 1],
            ['Licores y destilados', 'Piscos, licores y otros destilados.', 2],
            ['Snacks', 'Papitas y bocaditos.', 3],
            ['Bebidas', 'Agua y bebidas sin alcohol.', 4],
            ['Extras', 'Canchita con chicharrón y complementos.', 5],
            ['Implementos', 'Copas, vasos y otros artículos del local (no se venden).', 9],
        ];

        foreach ($categorias as [$nombre, $descripcion, $orden]) {
            Categoria::firstOrCreate(['nombre' => $nombre], ['descripcion' => $descripcion, 'orden_carta' => $orden, 'activo' => true]);
        }
    }
}
