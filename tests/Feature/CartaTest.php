<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Services\InventarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** RF-20 a RF-24: carta digital pública y su código QR (CU-16, CU-17, RN-14 a RN-17). */
class CartaTest extends TestCase
{
    use RefreshDatabase;

    private Categoria $vinos;

    protected function setUp(): void
    {
        parent::setUp();
        $this->vinos = Categoria::create(['nombre' => 'Vinos', 'orden_carta' => 1]);
    }

    private function producto(array $datos = [], int $stock = 0): Producto
    {
        $p = Producto::create($datos + [
            'categoria_id' => $this->vinos->id, 'nombre' => 'Vino Borgoña', 'tipo' => 'PRODUCTO',
            'caracteristicas' => 'Semiseco · 750 ml', 'precio' => 28, 'stock_minimo' => 6, 'visible_en_carta' => true,
        ]);
        if ($stock > 0) {
            $this->actingAs($this->admin());
            app(InventarioService::class)->registrarEntrada($p->id, 'COMPRA_LOCAL', $stock);
            auth()->logout();
        }

        return $p->fresh();
    }

    public function test_la_carta_es_publica_y_no_pide_iniciar_sesion(): void
    {
        $this->producto(stock: 37);

        $this->get('/carta')->assertOk()
            ->assertSee('Vino Borgoña')
            ->assertSee('Semiseco · 750 ml')
            ->assertSee('S/ 28.00');
        $this->assertGuest();
    }

    public function test_agrupa_los_productos_bajo_titulos_por_categoria_en_el_orden_definido(): void
    {
        $snacks = Categoria::create(['nombre' => 'Snacks', 'orden_carta' => 2]);
        $this->producto(['nombre' => 'Inca Chips', 'categoria_id' => $snacks->id, 'precio' => 3.5], 10);
        $this->producto(stock: 5);

        $this->get('/carta')->assertSeeInOrder(['Vinos', 'Vino Borgoña', 'Snacks', 'Inca Chips']);
    }

    public function test_muestra_agotado_sin_revelar_el_stock_exacto(): void
    {
        $this->producto(['nombre' => 'Vino Moscatel']);
        $this->producto(['nombre' => 'Vino Rosé'], stock: 37);

        $respuesta = $this->get('/carta');
        $respuesta->assertSee('Vino Moscatel')->assertSee('Agotado');
        $respuesta->assertDontSee('37'); // RN-15: nunca el stock exacto
    }

    public function test_no_muestra_implementos_productos_ocultos_ni_desactivados(): void
    {
        $this->producto(['nombre' => 'Copa de vino', 'tipo' => 'IMPLEMENTO', 'precio' => 0, 'visible_en_carta' => false], 20);
        $this->producto(['nombre' => 'Vino de reserva privada', 'visible_en_carta' => false], 5);
        $this->producto(['nombre' => 'Vino descontinuado', 'activo' => false]);
        $inactiva = Categoria::create(['nombre' => 'Temporada', 'orden_carta' => 5, 'activo' => false]);
        $this->producto(['nombre' => 'Vino de temporada', 'categoria_id' => $inactiva->id], 5);

        $this->get('/carta')->assertOk()
            ->assertDontSee('Copa de vino')
            ->assertDontSee('Vino de reserva privada')
            ->assertDontSee('Vino descontinuado')
            ->assertDontSee('Vino de temporada');
    }

    public function test_muestra_las_advertencias_de_la_ley_28681(): void
    {
        $this->get('/carta')->assertOk()
            ->assertSee('PROHIBIDA LA VENTA DE BEBIDAS ALCOHÓLICAS A MENORES DE 18 AÑOS')
            ->assertSee('TOMAR BEBIDAS ALCOHÓLICAS EN EXCESO ES DAÑINO');
    }

    public function test_es_una_sola_pagina_sin_buscador_ni_formularios(): void
    {
        $this->producto(stock: 3);

        $html = $this->get('/carta')->getContent();
        $this->assertStringNotContainsString('<form', $html);
        $this->assertStringNotContainsString('<input', $html);
    }

    public function test_un_cambio_de_precio_se_refleja_de_inmediato(): void
    {
        $p = $this->producto(stock: 3);
        $p->update(['precio' => 30]);

        $this->get('/carta')->assertSee('S/ 30.00')->assertDontSee('S/ 28.00');
    }

    public function test_el_administrador_ve_el_qr_que_apunta_a_la_carta(): void
    {
        $this->actingAs($this->admin())->get('/carta-digital')
            ->assertOk()
            ->assertSee(route('carta'))
            ->assertSee('<svg', false);
    }

    public function test_descarga_el_qr_en_svg_y_la_hoja_para_mesas_en_pdf(): void
    {
        $admin = $this->admin();

        $svg = $this->actingAs($admin)->get('/carta-digital/qr.svg');
        $svg->assertOk()->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertStringContainsString('<svg', $svg->getContent());

        $pdf = $this->actingAs($admin)->get('/carta-digital/hoja-mesas.pdf');
        $pdf->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $this->assertDatabaseHas('bitacora', ['modulo' => 'carta', 'accion' => 'código QR descargado']);
    }

    public function test_el_encargado_no_genera_el_qr(): void
    {
        $this->actingAs($this->encargado())->get('/carta-digital')->assertForbidden();
    }
}
