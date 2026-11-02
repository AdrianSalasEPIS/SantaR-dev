<?php

namespace Tests\Unit;

use App\Models\Usuario;
use PHPUnit\Framework\TestCase;

class UsuarioTest extends TestCase
{
    public function test_obtiene_las_iniciales_para_el_avatar(): void
    {
        $this->assertSame('ES', (new Usuario(['nombre' => 'Encargado de sede']))->iniciales());
        $this->assertSame('MQ', (new Usuario(['nombre' => '  maría   quispe ']))->iniciales());
        $this->assertSame('Á', (new Usuario(['nombre' => 'álvaro']))->iniciales());
    }
}
