<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Services\InventarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** RF-17 Kardex, RF-18 Generar reportes y RF-19 Exportar reportes (CU-14, CU-15). */
class ReporteTest extends TestCase
{
    use RefreshDatabase;

    private Producto $vino;

    private Producto $copa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->admin());
        $vinos = Categoria::create(['nombre' => 'Vinos', 'orden_carta' => 1]);
        $implementos = Categoria::create(['nombre' => 'Implementos', 'orden_carta' => 9]);
        $this->vino = Producto::create(['categoria_id' => $vinos->id, 'nombre' => 'Vino Borgoña', 'tipo' => 'PRODUCTO', 'precio' => 28, 'stock_minimo' => 6]);
        $this->copa = Producto::create(['categoria_id' => $implementos->id, 'nombre' => 'Copa de vino', 'tipo' => 'IMPLEMENTO', 'precio' => 0, 'stock_minimo' => 12]);

        $inventario = app(InventarioService::class);
        $inventario->registrarEntrada($this->vino->id, 'COMPRA_LOCAL', 20);
        $inventario->registrarSalida($this->vino->id, 'VENTA', 3);
        $inventario->registrarAbastecimiento(today()->toDateString(), 'G-0300', null, [['producto_id' => $this->copa->id, 'cantidad' => 24]]);
        $inventario->registrarIncidencia($this->copa->id, 'ROTURA', 2, 'Se cayeron al servir');
    }

    public function test_el_kardex_muestra_cada_movimiento_con_su_stock_resultante(): void
    {
        $this->get("/productos/{$this->vino->id}/kardex")->assertOk()
            ->assertSeeInOrder(['Compra local', '+20', '20', 'Venta', '−3', '17'])
            ->assertSee('Unidades ingresadas');
    }

    public function test_el_kardex_filtra_por_fechas_y_lo_consulta_el_encargado(): void
    {
        $this->actingAs($this->encargado())->get("/productos/{$this->vino->id}/kardex")->assertOk();
        $this->get("/productos/{$this->vino->id}/kardex?desde=".now()->addDay()->toDateString())
            ->assertSee('Este producto no tiene movimientos en ese rango de fechas.');
    }

    public function test_genera_los_cuatro_tipos_de_reporte(): void
    {
        $this->get('/reportes?tipo=existencias')->assertOk()->assertSee('Reporte de existencias')->assertSee('Vino Borgoña');
        $this->get('/reportes?tipo=movimientos')->assertOk()->assertSee('Reporte de movimientos')->assertSee('Venta');
        $this->get('/reportes?tipo=abastecimientos')->assertOk()->assertSee('G-0300');
        $this->get('/reportes?tipo=incidencias')->assertOk()->assertSee('Se cayeron al servir');
    }

    public function test_filtra_por_categoria_y_por_fechas(): void
    {
        $vinos = Categoria::where('nombre', 'Vinos')->value('id');

        $this->get("/reportes?tipo=existencias&categoria_id={$vinos}")->assertSee('Vino Borgoña')->assertDontSee('Copa de vino');
        $this->get('/reportes?tipo=movimientos&desde='.now()->addDay()->toDateString().'&hasta='.now()->addDays(2)->toDateString())
            ->assertSee('No hay datos para el período y los filtros elegidos.');
        $this->get('/reportes?tipo=movimientos&desde='.today()->toDateString().'&hasta='.now()->subDay()->toDateString())
            ->assertSessionHasErrors('hasta');
    }

    public function test_exporta_a_pdf_y_lo_registra_en_la_bitacora(): void
    {
        $respuesta = $this->get('/reportes/exportar/pdf?tipo=existencias');

        $respuesta->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $respuesta->getContent());
        $this->assertDatabaseHas('bitacora', ['modulo' => 'reportes', 'accion' => 'reporte exportado']);
    }

    public function test_exporta_a_excel(): void
    {
        $respuesta = $this->get('/reportes/exportar/excel?tipo=movimientos');

        $respuesta->assertOk();
        $this->assertStringContainsString('spreadsheetml', $respuesta->headers->get('Content-Type'));
        $this->assertStringContainsString('.xlsx', $respuesta->headers->get('Content-Disposition'));
    }

    public function test_solo_el_administrador_genera_reportes(): void
    {
        $this->actingAs($this->encargado())->get('/reportes')->assertForbidden();
        $this->actingAs($this->encargado())->get('/reportes/exportar/pdf')->assertForbidden();
    }

    public function test_no_acepta_formatos_ni_tipos_desconocidos(): void
    {
        $this->get('/reportes/exportar/docx')->assertNotFound();
        $this->get('/reportes?tipo=ventas')->assertSessionHasErrors('tipo');
    }
}
