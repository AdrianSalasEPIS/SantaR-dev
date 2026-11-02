<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** RF-07 Gestionar categorías (CU-06, RN-18). */
class CategoriaTest extends TestCase
{
    use RefreshDatabase;

    public function test_registra_una_categoria_con_su_orden_en_la_carta(): void
    {
        $this->actingAs($this->admin())->post('/categorias', [
            'nombre' => 'Vinos', 'descripcion' => 'Vinos por botella', 'orden_carta' => 1,
        ])->assertRedirect('/categorias');

        $this->assertDatabaseHas('categorias', ['nombre' => 'Vinos', 'orden_carta' => 1, 'activo' => true]);
        $this->assertDatabaseHas('bitacora', ['modulo' => 'catalogo', 'accion' => 'categoría creada']);
    }

    public function test_el_nombre_no_se_repite(): void
    {
        Categoria::create(['nombre' => 'Vinos', 'orden_carta' => 1]);

        $this->actingAs($this->admin())->post('/categorias', ['nombre' => 'Vinos', 'orden_carta' => 2])
            ->assertSessionHasErrors('nombre');
    }

    public function test_edita_una_categoria(): void
    {
        $categoria = Categoria::create(['nombre' => 'Snacks', 'orden_carta' => 3]);

        $this->actingAs($this->admin())->put("/categorias/{$categoria->id}", ['nombre' => 'Bocaditos', 'orden_carta' => 2])
            ->assertRedirect('/categorias');

        $this->assertSame('Bocaditos', $categoria->fresh()->nombre);
    }

    public function test_no_desactiva_una_categoria_con_productos_activos(): void
    {
        $categoria = Categoria::create(['nombre' => 'Vinos', 'orden_carta' => 1]);
        Producto::create(['categoria_id' => $categoria->id, 'nombre' => 'Vino Borgoña', 'tipo' => 'PRODUCTO', 'precio' => 28, 'stock_minimo' => 6]);

        $this->actingAs($this->admin())->patch("/categorias/{$categoria->id}/estado")->assertSessionHas('error');
        $this->assertTrue($categoria->fresh()->activo);
    }

    public function test_desactiva_una_categoria_sin_productos_activos(): void
    {
        $categoria = Categoria::create(['nombre' => 'Temporada', 'orden_carta' => 8]);

        $this->actingAs($this->admin())->patch("/categorias/{$categoria->id}/estado")->assertSessionHas('exito');
        $this->assertFalse($categoria->fresh()->activo);
    }

    public function test_el_encargado_de_sede_no_gestiona_categorias(): void
    {
        $this->actingAs($this->encargado())->get('/categorias')->assertForbidden();
    }
}
