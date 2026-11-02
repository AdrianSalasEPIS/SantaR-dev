<?php

namespace Tests\Feature;

use App\Models\Bitacora;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** RF-08 Gestionar productos e implementos (CU-07, RN-10, RN-14, RN-16). */
class ProductoTest extends TestCase
{
    use RefreshDatabase;

    private Categoria $categoria;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->categoria = Categoria::create(['nombre' => 'Vinos', 'orden_carta' => 1]);
    }

    private function datos(array $cambios = []): array
    {
        return $cambios + [
            'nombre' => 'Vino Borgoña',
            'tipo' => 'PRODUCTO',
            'categoria_id' => $this->categoria->id,
            'caracteristicas' => 'Semiseco · 750 ml',
            'precio' => '28.50',
            'stock_minimo' => 6,
            'visible_en_carta' => 1,
            'imagen' => UploadedFile::fake()->image('borgona.jpg', 1600, 1200),
        ];
    }

    public function test_registra_un_producto_con_imagen_optimizada_para_la_carta(): void
    {
        $this->actingAs($this->admin())->post('/productos', $this->datos())->assertRedirect('/productos');

        $producto = Producto::where('nombre', 'Vino Borgoña')->firstOrFail();
        $this->assertSame('28.50', $producto->precio);
        $this->assertSame(0, $producto->stock_actual);
        $this->assertTrue($producto->visible_en_carta);
        Storage::disk('public')->assertExists($producto->imagen);

        // La imagen se reduce a 800 px como máximo (RNF-03).
        [$ancho, $alto] = getimagesizefromstring(Storage::disk('public')->get($producto->imagen));
        $this->assertSame(800, max($ancho, $alto));

        $this->assertDatabaseHas('bitacora', ['modulo' => 'catalogo', 'accion' => 'producto creado']);
    }

    public function test_la_imagen_es_obligatoria_para_productos_visibles_en_la_carta(): void
    {
        $this->actingAs($this->admin())->post('/productos', $this->datos(['imagen' => null]))
            ->assertSessionHasErrors('imagen');
    }

    public function test_valida_tipo_y_tamano_de_la_imagen(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/productos', $this->datos(['imagen' => UploadedFile::fake()->create('menu.pdf', 100, 'application/pdf')]))
            ->assertSessionHasErrors('imagen');
        $this->actingAs($admin)->post('/productos', $this->datos(['imagen' => UploadedFile::fake()->image('grande.jpg', 1200, 1200)->size(3000)]))
            ->assertSessionHasErrors('imagen');
    }

    public function test_el_precio_de_un_producto_debe_ser_mayor_que_cero(): void
    {
        $this->actingAs($this->admin())->post('/productos', $this->datos(['precio' => 0]))
            ->assertSessionHasErrors('precio');
    }

    public function test_un_implemento_no_se_vende_ni_se_muestra_en_la_carta(): void
    {
        $this->actingAs($this->admin())->post('/productos', $this->datos([
            'nombre' => 'Copa de vino', 'tipo' => 'IMPLEMENTO', 'precio' => 15, 'visible_en_carta' => 1, 'imagen' => null,
        ]))->assertRedirect('/productos');

        $copa = Producto::where('nombre', 'Copa de vino')->firstOrFail();
        $this->assertFalse($copa->visible_en_carta);
        $this->assertSame('0.00', $copa->precio);
        $this->assertSame(0, Producto::visiblesEnCarta()->count());
    }

    public function test_el_stock_no_se_puede_modificar_desde_el_formulario(): void
    {
        $this->actingAs($this->admin())->post('/productos', $this->datos(['stock_actual' => 500]));

        $this->assertSame(0, Producto::where('nombre', 'Vino Borgoña')->value('stock_actual'));
    }

    public function test_edita_el_precio_y_lo_registra_en_la_bitacora(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/productos', $this->datos());
        $producto = Producto::firstOrFail();

        $this->actingAs($admin)->put("/productos/{$producto->id}", $this->datos(['precio' => '30', 'imagen' => null]))
            ->assertRedirect('/productos');

        $this->assertSame('30.00', $producto->fresh()->precio);
        $this->assertDatabaseHas('bitacora', ['accion' => 'producto editado']);
        $this->assertStringContainsString('S/ 28.50 → S/ 30.00', Bitacora::latest('id')->value('detalle'));
    }

    public function test_reemplazar_la_imagen_elimina_la_anterior(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/productos', $this->datos());
        $producto = Producto::firstOrFail();
        $anterior = $producto->imagen;

        $this->actingAs($admin)->put("/productos/{$producto->id}", $this->datos(['imagen' => UploadedFile::fake()->image('nueva.png', 600, 600)]));

        Storage::disk('public')->assertMissing($anterior);
        Storage::disk('public')->assertExists($producto->fresh()->imagen);
    }

    public function test_desactiva_un_producto_y_deja_de_ser_visible_en_la_carta(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/productos', $this->datos());
        $producto = Producto::firstOrFail();

        $this->actingAs($admin)->patch("/productos/{$producto->id}/estado")->assertSessionHas('exito');

        $this->assertFalse($producto->fresh()->activo);
        $this->assertSame(0, Producto::visiblesEnCarta()->count());
        $this->assertDatabaseHas('productos', ['id' => $producto->id]); // se conserva el historial
    }

    public function test_no_admite_una_categoria_desactivada_para_productos_nuevos(): void
    {
        $inactiva = Categoria::create(['nombre' => 'Antigua', 'orden_carta' => 9, 'activo' => false]);

        $this->actingAs($this->admin())->post('/productos', $this->datos(['categoria_id' => $inactiva->id]))
            ->assertSessionHasErrors('categoria_id');
    }

    public function test_busca_y_filtra_productos(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/productos', $this->datos());
        $this->actingAs($admin)->post('/productos', $this->datos(['nombre' => 'Copa de vino', 'tipo' => 'IMPLEMENTO', 'imagen' => null]));

        $this->get('/productos?tipo=IMPLEMENTO')->assertOk()->assertSee('Copa de vino')->assertDontSee('Vino Borgoña');
        $this->get('/productos?buscar=borgo')->assertOk()->assertSee('Vino Borgoña');
    }

    public function test_el_encargado_de_sede_no_modifica_productos_ni_precios(): void
    {
        $encargado = $this->encargado();

        $this->actingAs($encargado)->get('/productos')->assertForbidden();
        $this->actingAs($encargado)->post('/productos', $this->datos())->assertForbidden();
    }
}
