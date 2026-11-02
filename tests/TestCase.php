<?php

namespace Tests;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected const PASSWORD = 'Clave2026';

    protected function rol(string $nombre): Rol
    {
        return Rol::firstOrCreate(['nombre' => $nombre]);
    }

    protected function crearUsuario(string $rol = Rol::ADMINISTRADOR, array $datos = []): Usuario
    {
        static $n = 0;
        $n++;

        return Usuario::create($datos + [
            'rol_id' => $this->rol($rol)->id,
            'nombre' => "Usuario {$n}",
            'correo' => "usuario{$n}@santar.test",
            'password' => self::PASSWORD,
            'activo' => true,
        ]);
    }

    protected function admin(): Usuario
    {
        return $this->crearUsuario(Rol::ADMINISTRADOR);
    }

    protected function encargado(): Usuario
    {
        return $this->crearUsuario(Rol::ENCARGADO);
    }
}
