<?php

namespace Tests\Feature;

use App\Exceptions\ReglaNegocioException;
use App\Exceptions\StockInsuficienteException;
use App\Models\Abastecimiento;
use App\Models\Categoria;
use App\Models\Incidencia;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Services\InventarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** RF-09 a RF-14: reglas del servicio de inventario (RN-06 a RN-13). */
class InventarioServiceTest extends TestCase
{
    use RefreshDatabase;

    private InventarioService $inventario;

    private Categoria $categoria;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->admin());
        $this->inventario = app(InventarioService::class);
        $this->categoria = Categoria::create(['nombre' => 'Vinos', 'orden_carta' => 1]);
    }

    private function producto(array $datos = []): Producto
    {
        static $n = 0;
        $n++;

        return Producto::create($datos + [
            'categoria_id' => $this->categoria->id, 'nombre' => "Producto {$n}", 'tipo' => 'PRODUCTO',
            'precio' => 28, 'stock_minimo' => 5,
        ]);
    }

    public function test_una_entrada_suma_al_stock_y_queda_trazada(): void
    {
        $p = $this->producto();

        $m = $this->inventario->registrarEntrada($p->id, 'COMPRA_LOCAL', 12, 'Compra en mercado');

        $this->assertSame(12, $p->fresh()->stock_actual);
        $this->assertSame('ENTRADA', $m->tipo);
        $this->assertSame(12, $m->stock_resultante);
        $this->assertDatabaseHas('bitacora', ['modulo' => 'inventario', 'accion' => 'entrada registrada']);
    }

    public function test_una_salida_descuenta_el_stock(): void
    {
        $p = $this->producto();
        $this->inventario->registrarEntrada($p->id, 'COMPRA_LOCAL', 10);

        $m = $this->inventario->registrarSalida($p->id, 'VENTA', 3);

        $this->assertSame(7, $p->fresh()->stock_actual);
        $this->assertSame(3, $m->cantidad);
        $this->assertSame(7, $m->stock_resultante);
    }

    public function test_no_permite_sacar_mas_que_el_stock_disponible(): void
    {
        $p = $this->producto();
        $this->inventario->registrarEntrada($p->id, 'COMPRA_LOCAL', 2);

        try {
            $this->inventario->registrarSalida($p->id, 'VENTA', 3);
            $this->fail('Debió rechazar la salida (RN-07).');
        } catch (StockInsuficienteException) {
        }

        $this->assertSame(2, $p->fresh()->stock_actual);
        $this->assertSame(1, Movimiento::count());
    }

    public function test_los_implementos_no_tienen_salidas(): void
    {
        $copa = $this->producto(['tipo' => 'IMPLEMENTO', 'precio' => 0]);
        $this->inventario->registrarEntrada($copa->id, 'COMPRA_LOCAL', 5);

        $this->expectException(ReglaNegocioException::class);
        $this->inventario->registrarSalida($copa->id, 'VENTA', 1);
    }

    public function test_las_cantidades_deben_ser_mayores_que_cero(): void
    {
        $p = $this->producto();

        $this->expectException(ReglaNegocioException::class);
        $this->inventario->registrarEntrada($p->id, 'COMPRA_LOCAL', 0);
    }

    public function test_no_registra_movimientos_de_productos_desactivados(): void
    {
        $p = $this->producto(['activo' => false]);

        $this->expectException(ReglaNegocioException::class);
        $this->inventario->registrarEntrada($p->id, 'COMPRA_LOCAL', 3);
    }

    public function test_el_ajuste_corrige_el_stock_segun_el_conteo_fisico(): void
    {
        $p = $this->producto();
        $this->inventario->registrarEntrada($p->id, 'COMPRA_LOCAL', 10);

        $menos = $this->inventario->registrarAjuste($p->id, 8, 'Conteo de cierre de semana');
        $this->assertSame('AJUSTE_NEGATIVO', $menos->motivo);
        $this->assertSame(2, $menos->cantidad);
        $this->assertSame(8, $p->fresh()->stock_actual);

        $mas = $this->inventario->registrarAjuste($p->id, 11, 'Se encontraron 3 botellas');
        $this->assertSame('AJUSTE_POSITIVO', $mas->motivo);
        $this->assertSame(11, $p->fresh()->stock_actual);

        $this->assertNull($this->inventario->registrarAjuste($p->id, 11, 'Sin diferencia'));
    }

    public function test_el_abastecimiento_suma_los_productos_repetidos_y_guarda_su_detalle(): void
    {
        $vino = $this->producto();
        $copa = $this->producto(['tipo' => 'IMPLEMENTO', 'precio' => 0]);

        $a = $this->inventario->registrarAbastecimiento(today()->toDateString(), 'G-0158', null, [
            ['producto_id' => $vino->id, 'cantidad' => 6],
            ['producto_id' => $copa->id, 'cantidad' => 12],
            ['producto_id' => $vino->id, 'cantidad' => 4],
        ]);

        $this->assertSame(10, $vino->fresh()->stock_actual);
        $this->assertSame(12, $copa->fresh()->stock_actual);
        $this->assertCount(2, $a->detalles);
        $this->assertSame(2, Movimiento::where('abastecimiento_id', $a->id)->count());
        $this->assertDatabaseHas('bitacora', ['accion' => 'abastecimiento registrado']);
    }

    public function test_el_abastecimiento_es_todo_o_nada(): void
    {
        $vino = $this->producto();
        $inactivo = $this->producto(['activo' => false]);

        try {
            $this->inventario->registrarAbastecimiento(today()->toDateString(), null, null, [
                ['producto_id' => $vino->id, 'cantidad' => 6],
                ['producto_id' => $inactivo->id, 'cantidad' => 3],
            ]);
            $this->fail('Debió rechazar el abastecimiento.');
        } catch (ReglaNegocioException) {
        }

        $this->assertSame(0, $vino->fresh()->stock_actual);
        $this->assertSame(0, Abastecimiento::count());
        $this->assertSame(0, Movimiento::count());
    }

    public function test_una_incidencia_descuenta_el_stock_incluso_de_implementos(): void
    {
        $copa = $this->producto(['tipo' => 'IMPLEMENTO', 'precio' => 0]);
        $this->inventario->registrarEntrada($copa->id, 'COMPRA_LOCAL', 24);

        $i = $this->inventario->registrarIncidencia($copa->id, 'ROTURA', 1, 'Copa rota al lavar');

        $this->assertSame(23, $copa->fresh()->stock_actual);
        $this->assertDatabaseHas('movimientos', ['incidencia_id' => $i->id, 'tipo' => 'INCIDENCIA', 'stock_resultante' => 23]);
    }

    public function test_una_incidencia_no_supera_el_stock(): void
    {
        $p = $this->producto();

        $this->expectException(StockInsuficienteException::class);
        $this->inventario->registrarIncidencia($p->id, 'PERDIDA', 1, 'No se encontró');
    }

    public function test_los_movimientos_no_se_modifican_ni_se_eliminan(): void
    {
        $p = $this->producto();
        $m = $this->inventario->registrarEntrada($p->id, 'COMPRA_LOCAL', 5);

        try {
            $m->update(['cantidad' => 50]);
            $this->fail('Debió impedir la modificación (RN-09).');
        } catch (ReglaNegocioException) {
        }

        $this->expectException(ReglaNegocioException::class);
        $m->delete();
    }

    public function test_el_stock_coincide_siempre_con_la_suma_de_sus_movimientos(): void
    {
        $p = $this->producto();
        $this->inventario->registrarEntrada($p->id, 'COMPRA_LOCAL', 20);
        $this->inventario->registrarSalida($p->id, 'VENTA', 4);
        $this->inventario->registrarIncidencia($p->id, 'ROTURA', 1, 'Botella rota');
        $this->inventario->registrarAbastecimiento(today()->toDateString(), null, null, [['producto_id' => $p->id, 'cantidad' => 6]]);
        $this->inventario->registrarAjuste($p->id, 20, 'Conteo físico');

        $suma = Movimiento::where('producto_id', $p->id)->get()
            ->sum(fn (Movimiento $m) => $m->suma() ? $m->cantidad : -$m->cantidad);

        $this->assertSame($p->fresh()->stock_actual, $suma); // RN-06
        $this->assertSame(20, $suma);
    }

    public function test_detecta_el_stock_minimo(): void
    {
        $p = $this->producto(['stock_minimo' => 5]);
        $this->inventario->registrarEntrada($p->id, 'COMPRA_LOCAL', 6);
        $this->assertFalse($this->inventario->verificarStockMinimo($p->fresh()));

        $this->inventario->registrarSalida($p->id, 'VENTA', 1);
        $this->assertTrue($this->inventario->verificarStockMinimo($p->fresh())); // RN-11: igual al mínimo
        $this->assertSame('Alerta', $p->fresh()->estado());

        $this->inventario->registrarSalida($p->id, 'VENTA', 5);
        $this->assertSame('Agotado', $p->fresh()->estado());
    }

    public function test_los_tipos_de_incidencia_son_los_del_srs(): void
    {
        $this->assertSame(['PERDIDA', 'ROTURA', 'OTRO'], array_keys(Incidencia::TIPOS));
    }
}
