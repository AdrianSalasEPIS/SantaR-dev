<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Datos base: roles, la cuenta del encargado (rol Administrador) y categorías.
     * El catálogo de demostración se carga solo en el entorno local o si DATOS_DEMO=true
     * (por ejemplo, en el ambiente de pruebas en línea); nunca en la base del cliente.
     */
    public function run(): void
    {
        $this->call([RolSeeder::class, UsuarioSeeder::class, CategoriaSeeder::class]);

        $demo = app()->environment('local') || config('app.datos_demo');
        if ($demo && ! app()->runningUnitTests()) {
            $this->call(DemoSeeder::class);
        }
    }
}
