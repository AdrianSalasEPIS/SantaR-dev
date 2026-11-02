<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\InventarioService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Datos de DEMOSTRACIÓN (solo entorno local o de pruebas): catálogo de ejemplo con imágenes y
 * algunos movimientos. Todo el stock se genera con movimientos, igual que en el uso real (RN-06).
 * No hace nada si ya existen productos registrados.
 */
class DemoSeeder extends Seeder
{
    public function run(InventarioService $inventario): void
    {
        if (Producto::exists()) {
            return;
        }

        Auth::setUser(Usuario::firstOrFail());
        $cat = Categoria::pluck('id', 'nombre');

        // [imagen, nombre, categoría, tipo, características, precio, stock mínimo, stock inicial]
        $productos = [
            ['vino-borgona', 'Vino Borgoña', 'Vinos', 'PRODUCTO', 'Semiseco · 750 ml', 28.00, 6, 14],
            ['vino-rose', 'Vino Rosé', 'Vinos', 'PRODUCTO', 'Dulce · 750 ml', 32.00, 4, 9],
            ['vino-tinto-seco', 'Vino Tinto Seco', 'Vinos', 'PRODUCTO', 'Reserva · 750 ml', 45.00, 4, 6],
            ['vino-moscatel', 'Vino Moscatel', 'Vinos', 'PRODUCTO', 'Dulce · 750 ml', 30.00, 4, 3],
            ['inca-chips-clasica', 'Inca Chips Clásica', 'Snacks', 'PRODUCTO', 'Bolsa · 40 g', 3.50, 10, 30],
            ['inca-chips-picante', 'Inca Chips Picante', 'Snacks', 'PRODUCTO', 'Bolsa · 40 g', 3.50, 10, 12],
            ['agua-sin-gas', 'Agua sin gas', 'Bebidas', 'PRODUCTO', 'Botella · 625 ml', 2.50, 12, 24],
            ['canchita-chicharron', 'Canchita con chicharrón', 'Extras', 'PRODUCTO', 'Bolsa · 150 g', 6.00, 5, 10],
            [null, 'Copa de vino', 'Implementos', 'IMPLEMENTO', 'Cristal', 0, 12, 24],
            [null, 'Vaso de vidrio', 'Implementos', 'IMPLEMENTO', null, 0, 12, 20],
        ];

        $ids = [];
        foreach ($productos as [$img, $nombre, $categoria, $tipo, $carac, $precio, $minimo, $stock]) {
            $ruta = null;
            if ($img) {
                $ruta = "productos/demo-{$img}.jpg";
                Storage::disk('public')->put($ruta, file_get_contents(__DIR__."/imagenes_demo/{$img}.jpg"));
            }

            $p = Producto::create([
                'categoria_id' => $cat[$categoria], 'nombre' => $nombre, 'tipo' => $tipo, 'caracteristicas' => $carac,
                'precio' => $precio, 'imagen' => $ruta, 'stock_minimo' => $minimo,
                'visible_en_carta' => $tipo === 'PRODUCTO', 'activo' => true,
            ]);
            $inventario->registrarStockInicial($p->id, $stock);
            $ids[$nombre] = $p->id;
        }

        // Algunas operaciones del día para que el panel y las alertas muestren información.
        $inventario->registrarSalida($ids['Vino Borgoña'], 'VENTA', 2);
        $inventario->registrarSalida($ids['Inca Chips Picante'], 'VENTA', 4);
        $inventario->registrarSalida($ids['Vino Moscatel'], 'VENTA', 3);
        $inventario->registrarSalida($ids['Agua sin gas'], 'CONSUMO_INTERNO', 1);
        $inventario->registrarAbastecimiento(today()->toDateString(), 'G-0158', 'Envío semanal de la bodega principal', [
            ['producto_id' => $ids['Vino Rosé'], 'cantidad' => 6],
            ['producto_id' => $ids['Canchita con chicharrón'], 'cantidad' => 10],
        ]);
        $inventario->registrarIncidencia($ids['Copa de vino'], 'ROTURA', 1, 'Copa rota al lavar');

        Auth::logout();
    }
}
