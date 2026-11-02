<?php

namespace Tests\Feature;

use App\Models\Bitacora;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

/** RF-05 Registrar bitácora y RF-06 Consultar bitácora (CU-05, RN-20). */
class BitacoraTest extends TestCase
{
    use RefreshDatabase;

    public function test_registra_usuario_modulo_accion_ip_y_fecha(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        Bitacora::registrar('catalogo', 'categoría creada', 'Vinos');

        $registro = Bitacora::latest('id')->first();
        $this->assertSame($admin->id, $registro->usuario_id);
        $this->assertSame('catalogo', $registro->modulo);
        $this->assertSame('categoría creada', $registro->accion);
        $this->assertSame('127.0.0.1', $registro->ip);
        $this->assertNotNull($registro->fecha_hora);
    }

    public function test_los_registros_no_se_modifican_ni_se_eliminan(): void
    {
        $this->actingAs($this->admin());
        Bitacora::registrar('seguridad', 'prueba');
        $registro = Bitacora::latest('id')->first();

        $this->expectException(LogicException::class);
        $registro->update(['accion' => 'alterado']);
    }

    public function test_el_administrador_consulta_y_filtra_la_bitacora(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        Bitacora::registrar('catalogo', 'producto creado', 'Vino Borgoña');
        Bitacora::registrar('usuarios', 'usuario creado', 'otra persona');

        $this->get('/bitacora?modulo=catalogo')
            ->assertOk()
            ->assertSee('Vino Borgoña')
            ->assertDontSee('otra persona');

        $this->get('/bitacora?desde='.now()->addDay()->toDateString())
            ->assertOk()
            ->assertSee('No hay registros con esos filtros.');
    }

    public function test_valida_los_filtros(): void
    {
        $this->actingAs($this->admin())->get('/bitacora?modulo=inexistente')->assertSessionHasErrors('modulo');
    }

    public function test_el_encargado_de_sede_no_consulta_la_bitacora(): void
    {
        $this->actingAs($this->encargado())->get('/bitacora')->assertForbidden();
    }
}
