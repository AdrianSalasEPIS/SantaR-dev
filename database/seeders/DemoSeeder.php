<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Datos de DEMOSTRACIÓN (solo entorno local o de pruebas): catálogo de ejemplo con imágenes.
 * Los productos se crean con stock 0: el stock solo cambia con movimientos de inventario (RN-06).
 * No hace nada si ya existen productos registrados.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Producto::exists()) {
            return;
        }

        $cat = Categoria::pluck('id', 'nombre');

        // [imagen, nombre, categoría, tipo, características, precio, stock mínimo]
        $productos = [
            ['vino-borgona', 'Vino Borgoña', 'Vinos', 'PRODUCTO', 'Semiseco · 750 ml', 28.00, 6],
            ['vino-rose', 'Vino Rosé', 'Vinos', 'PRODUCTO', 'Dulce · 750 ml', 32.00, 4],
            ['vino-tinto-seco', 'Vino Tinto Seco', 'Vinos', 'PRODUCTO', 'Reserva · 750 ml', 45.00, 4],
            ['vino-moscatel', 'Vino Moscatel', 'Vinos', 'PRODUCTO', 'Dulce · 750 ml', 30.00, 4],
            ['inca-chips-clasica', 'Inca Chips Clásica', 'Snacks', 'PRODUCTO', 'Bolsa · 40 g', 3.50, 10],
            ['inca-chips-picante', 'Inca Chips Picante', 'Snacks', 'PRODUCTO', 'Bolsa · 40 g', 3.50, 10],
            ['agua-sin-gas', 'Agua sin gas', 'Bebidas', 'PRODUCTO', 'Botella · 625 ml', 2.50, 12],
            ['canchita-chicharron', 'Canchita con chicharrón', 'Extras', 'PRODUCTO', 'Bolsa · 150 g', 6.00, 5],
            [null, 'Copa de vino', 'Implementos', 'IMPLEMENTO', 'Cristal', 0, 12],
            [null, 'Vaso de vidrio', 'Implementos', 'IMPLEMENTO', null, 0, 12],
        ];

        foreach ($productos as [$img, $nombre, $categoria, $tipo, $carac, $precio, $minimo]) {
            $ruta = null;
            if ($img) {
                $ruta = "productos/demo-{$img}.jpg";
                Storage::disk('public')->put($ruta, file_get_contents(__DIR__."/imagenes_demo/{$img}.jpg"));
            }

            Producto::create([
                'categoria_id' => $cat[$categoria], 'nombre' => $nombre, 'tipo' => $tipo, 'caracteristicas' => $carac,
                'precio' => $precio, 'imagen' => $ruta, 'stock_minimo' => $minimo,
                'visible_en_carta' => $tipo === 'PRODUCTO', 'activo' => true,
            ]);
        }
    }
}
