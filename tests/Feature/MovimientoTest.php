<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Services\InventarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** RF-09 a RF-16 desde las pantallas: CU-08 a CU-13 (rol de cada usuario, mensajes y alertas). */
class MovimientoTest extends TestCase
{
    use RefreshDatabase;

    private Categoria $categoria;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoria = Categoria::create(['nombre' => 'Vinos', 'orden_carta' => 1]);
    }

    private function producto(array $datos = [], int $stock = 0): Producto
    {
        static $n = 0;
        $n++;
        $p = Producto::create($datos + [
            'categoria_id' => $this->categoria->id, 'nombre' => "Vino {$n}", 'tipo' => 'PRODUCTO',
            'precio' => 28, 'stock_minimo' => 5,
        ]);
        if ($stock > 0) {
            $this->actingAs($this->admin());
            app(InventarioService::class)->registrarEntrada($p->id, 'COMPRA_LOCAL', $stock);
        }

        return $p->fresh();
    }

    public function test_el_encargado_registra_una_entrada(): void
    {
        $p = $this->producto();

        $this->actingAs($this->encargado())->get('/entradas/registrar')->assertOk()->assertSee($p->nombre);
        $this->post('/entradas', ['producto_id' => $p->id, 'motivo' => 'DEVOLUCION', 'cantidad' => 4])
            ->assertRedirect('/entradas/registrar')
            ->assertSessionHas('exito');

        $this->assertSame(4, $p->fresh()->stock_actual);
    }

    public function test_registra_una_venta_y_avisa_si_queda_en_stock_minimo(): void
    {
        $p = $this->producto(stock: 8);

        $this->actingAs($this->encargado())
            ->post('/salidas', ['producto_id' => $p->id, 'motivo' => 'VENTA', 'cantidad' => 3])
            ->assertRedirect('/salidas/registrar')
            ->assertSessionHas('exito')
            ->assertSessionHas('alerta');

        $this->assertSame(5, $p->fresh()->stock_actual);
    }

    public function test_avisa_cuando_el_producto_queda_agotado(): void
    {
        $p = $this->producto(stock: 2);

        $this->actingAs($this->encargado())
            ->post('/salidas', ['producto_id' => $p->id, 'motivo' => 'VENTA', 'cantidad' => 2])
            ->assertSessionHas('alerta', "«{$p->nombre}» quedó AGOTADO.");
    }

    public function test_muestra_error_si_el_stock_no_alcanza(): void
    {
        $p = $this->producto(stock: 1);

        $this->actingAs($this->encargado())->from('/salidas/registrar')
            ->post('/salidas', ['producto_id' => $p->id, 'motivo' => 'VENTA', 'cantidad' => 5])
            ->assertRedirect('/salidas/registrar')
            ->assertSessionHasErrors('cantidad');

        $this->assertSame(1, $p->fresh()->stock_actual);
    }

    public function test_la_salida_no_acepta_implementos_ni_cantidades_invalidas(): void
    {
        $copa = $this->producto(['tipo' => 'IMPLEMENTO', 'precio' => 0], stock: 5);
        $vino = $this->producto(stock: 5);
        $encargado = $this->encargado();

        $this->actingAs($encargado)->post('/salidas', ['producto_id' => $copa->id, 'motivo' => 'VENTA', 'cantidad' => 1])
            ->assertSessionHasErrors('producto_id');
        $this->actingAs($encargado)->post('/salidas', ['producto_id' => $vino->id, 'motivo' => 'VENTA', 'cantidad' => 0])
            ->assertSessionHasErrors('cantidad');
        $this->actingAs($encargado)->post('/salidas', ['producto_id' => $vino->id, 'motivo' => 'VENTA', 'cantidad' => 1.5])
            ->assertSessionHasErrors('cantidad');
        $this->actingAs($encargado)->post('/salidas', ['producto_id' => $vino->id, 'motivo' => 'REGALO', 'cantidad' => 1])
            ->assertSessionHasErrors('motivo');
    }

    public function test_solo_el_administrador_registra_ajustes(): void
    {
        $p = $this->producto(stock: 10);

        $this->actingAs($this->encargado())->get('/ajustes/registrar')->assertForbidden();
        $this->actingAs($this->encargado())->post('/ajustes', ['producto_id' => $p->id, 'stock_contado' => 9, 'motivo' => 'Conteo físico'])
            ->assertForbidden();

        $this->actingAs($this->admin())->post('/ajustes', ['producto_id' => $p->id, 'stock_contado' => 9, 'motivo' => 'Conteo físico'])
            ->assertSessionHas('exito');
        $this->assertSame(9, $p->fresh()->stock_actual);
    }

    public function test_el_ajuste_exige_motivo_e_informa_si_no_hay_diferencia(): void
    {
        $p = $this->producto(stock: 10);
        $admin = $this->admin();

        $this->actingAs($admin)->post('/ajustes', ['producto_id' => $p->id, 'stock_contado' => 9, 'motivo' => ''])
            ->assertSessionHasErrors('motivo');
        $this->actingAs($admin)->post('/ajustes', ['producto_id' => $p->id, 'stock_contado' => 10, 'motivo' => 'Conteo de cierre'])
            ->assertSessionHas('info');
    }

    public function test_registra_un_abastecimiento_ignorando_filas_vacias(): void
    {
        $vino = $this->producto();
        $copa = $this->producto(['tipo' => 'IMPLEMENTO', 'precio' => 0]);

        $respuesta = $this->actingAs($this->encargado())->post('/abastecimientos', [
            'fecha_recepcion' => today()->toDateString(),
            'nro_guia' => 'G-0200',
            'items' => [
                ['producto_id' => $vino->id, 'cantidad' => 12],
                ['producto_id' => '', 'cantidad' => ''],
                ['producto_id' => $copa->id, 'cantidad' => 24],
            ],
        ]);

        $respuesta->assertSessionHas('exito');
        $this->assertSame(12, $vino->fresh()->stock_actual);
        $this->assertSame(24, $copa->fresh()->stock_actual);
        $this->get('/abastecimientos')->assertOk()->assertSee('G-0200');
    }

    public function test_el_abastecimiento_exige_productos_y_fecha_valida(): void
    {
        $encargado = $this->encargado();

        $this->actingAs($encargado)->post('/abastecimientos', ['fecha_recepcion' => today()->toDateString(), 'items' => []])
            ->assertSessionHasErrors('items');
        $this->actingAs($encargado)->post('/abastecimientos', [
            'fecha_recepcion' => today()->addDay()->toDateString(),
            'items' => [['producto_id' => $this->producto()->id, 'cantidad' => 1]],
        ])->assertSessionHasErrors('fecha_recepcion');
    }

    public function test_registra_una_incidencia_con_descripcion_obligatoria(): void
    {
        $copa = $this->producto(['tipo' => 'IMPLEMENTO', 'precio' => 0], stock: 10);
        $encargado = $this->encargado();

        $this->actingAs($encargado)->post('/incidencias', ['producto_id' => $copa->id, 'tipo' => 'ROTURA', 'cantidad' => 1, 'descripcion' => ''])
            ->assertSessionHasErrors('descripcion');

        $this->actingAs($encargado)->post('/incidencias', ['producto_id' => $copa->id, 'tipo' => 'ROTURA', 'cantidad' => 2, 'descripcion' => 'Se cayeron al servir'])
            ->assertRedirect('/incidencias');

        $this->assertSame(8, $copa->fresh()->stock_actual);
        $this->get('/incidencias')->assertOk()->assertSee('Se cayeron al servir');
    }

    public function test_consulta_existencias_con_filtros_de_estado(): void
    {
        $agotado = $this->producto(['nombre' => 'Pisco Quebranta']);
        $enAlerta = $this->producto(['nombre' => 'Vino Rosé'], stock: 3);
        $normal = $this->producto(['nombre' => 'Vino Borgoña'], stock: 20);
        $this->actingAs($this->encargado());

        $this->get('/existencias')->assertOk()->assertSeeInOrder([$agotado->nombre, $enAlerta->nombre, $normal->nombre]);
        $this->get('/existencias?estado=agotado')->assertSee($agotado->nombre)->assertDontSee($normal->nombre);
        $this->get('/existencias?estado=alerta')->assertSee($enAlerta->nombre)->assertDontSee($agotado->nombre);
        $this->get('/existencias?buscar=borgo')->assertSee($normal->nombre)->assertDontSee($enAlerta->nombre);
    }

    public function test_el_panel_muestra_las_alertas_y_los_ultimos_movimientos(): void
    {
        $p = $this->producto(['nombre' => 'Vino Moscatel'], stock: 3);

        $this->actingAs($this->encargado())->get('/panel')
            ->assertOk()
            ->assertSee('Alertas de stock mínimo')
            ->assertSee('Vino Moscatel')
            ->assertSee('Últimos movimientos');
    }

    public function test_el_stock_inicial_de_un_producto_nuevo_se_registra_como_entrada(): void
    {
        $this->actingAs($this->admin())->post('/productos', [
            'nombre' => 'Copa de vino', 'tipo' => 'IMPLEMENTO', 'categoria_id' => $this->categoria->id,
            'stock_minimo' => 12, 'stock_inicial' => 24,
        ])->assertRedirect('/productos');

        $copa = Producto::where('nombre', 'Copa de vino')->firstOrFail();
        $this->assertSame(24, $copa->stock_actual);
        $this->assertDatabaseHas('movimientos', ['producto_id' => $copa->id, 'tipo' => 'ENTRADA', 'motivo' => 'STOCK_INICIAL', 'cantidad' => 24]);
    }

    public function test_al_editar_un_producto_no_se_puede_cambiar_el_stock(): void
    {
        $p = $this->producto(['tipo' => 'IMPLEMENTO', 'precio' => 0], stock: 5);

        $this->actingAs($this->admin())->put("/productos/{$p->id}", [
            'nombre' => $p->nombre, 'tipo' => 'IMPLEMENTO', 'categoria_id' => $this->categoria->id,
            'stock_minimo' => 5, 'stock_inicial' => 100,
        ])->assertSessionHasErrors('stock_inicial');

        $this->assertSame(5, $p->fresh()->stock_actual);
        $this->assertSame(1, Movimiento::where('producto_id', $p->id)->count());
    }
}
