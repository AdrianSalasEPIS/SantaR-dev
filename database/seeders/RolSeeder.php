<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        Rol::firstOrCreate(['nombre' => Rol::ADMINISTRADOR]);
        Rol::firstOrCreate(['nombre' => Rol::ENCARGADO]); // reservado para un trabajador adicional
    }
}
