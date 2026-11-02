<?php

namespace Tests\Feature;

use App\Models\Bitacora;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/** RF-01 Iniciar sesión y RF-02 Cerrar sesión (CU-01, CU-02, RN-01, RN-03, RN-05). */
class SesionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('usuario1@santar.test|127.0.0.1');
    }

    public function test_muestra_el_formulario_de_inicio_de_sesion(): void
    {
        $this->get('/login')->assertOk()->assertSee('Iniciar sesión');
    }

    public function test_las_rutas_internas_exigen_sesion(): void
    {
        $this->get('/panel')->assertRedirect('/login');
        $this->get('/usuarios')->assertRedirect('/login');
    }

    public function test_no_existe_registro_libre_de_usuarios(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_ingresa_con_credenciales_validas_y_registra_la_bitacora(): void
    {
        $usuario = $this->admin();

        $this->post('/login', ['correo' => strtoupper($usuario->correo), 'password' => self::PASSWORD])
            ->assertRedirect('/panel');

        $this->assertAuthenticatedAs($usuario);
        $this->assertNotNull($usuario->fresh()->ultimo_acceso);
        $this->assertDatabaseHas('bitacora', ['usuario_id' => $usuario->id, 'accion' => 'inicio de sesión']);
    }

    public function test_rechaza_credenciales_incorrectas_con_mensaje_generico(): void
    {
        $usuario = $this->admin();

        $this->from('/login')->post('/login', ['correo' => $usuario->correo, 'password' => 'Incorrecta1'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['correo' => 'Usuario o contraseña incorrectos.']);

        $this->assertGuest();
        $this->assertDatabaseHas('bitacora', ['accion' => 'intento de ingreso fallido']);
    }

    public function test_una_cuenta_desactivada_no_puede_ingresar(): void
    {
        $usuario = $this->crearUsuario(datos: ['activo' => false]);

        $this->post('/login', ['correo' => $usuario->correo, 'password' => self::PASSWORD])
            ->assertSessionHasErrors(['correo' => 'Su cuenta no está habilitada. Comuníquese con el administrador.']);

        $this->assertGuest();
    }

    public function test_bloquea_el_acceso_15_minutos_tras_5_intentos_fallidos(): void
    {
        $usuario = $this->admin();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['correo' => $usuario->correo, 'password' => 'Incorrecta1']);
        }

        // Incluso con la contraseña correcta, el acceso queda bloqueado.
        $respuesta = $this->post('/login', ['correo' => $usuario->correo, 'password' => self::PASSWORD]);
        $respuesta->assertSessionHasErrors('correo');
        $this->assertStringContainsString('Demasiados intentos fallidos', session('errors')->first('correo'));
        $this->assertGuest();
    }

    public function test_cierra_sesion_y_lo_registra(): void
    {
        $usuario = $this->admin();

        $this->actingAs($usuario)->post('/logout')
            ->assertRedirect('/login')
            ->assertSessionHas('estado');

        $this->assertGuest();
        $this->assertSame('cierre de sesión', Bitacora::latest('id')->value('accion'));
    }

    public function test_cierre_por_inactividad_muestra_el_aviso(): void
    {
        $this->actingAs($this->admin())->post('/logout', ['inactividad' => 1])
            ->assertRedirect('/login')
            ->assertSessionHas('aviso', 'Su sesión se cerró tras 15 minutos de inactividad.');

        $this->assertSame('cierre por inactividad', Bitacora::latest('id')->value('accion'));
    }

    public function test_la_sesion_dura_15_minutos_de_inactividad(): void
    {
        $this->assertSame(15, (int) config('session.lifetime'));
    }

    public function test_una_cuenta_desactivada_pierde_el_acceso_aunque_tenga_sesion_abierta(): void
    {
        $usuario = $this->admin();
        $this->actingAs($usuario);
        $usuario->update(['activo' => false]);

        $this->get('/panel')->assertRedirect('/login');
        $this->assertGuest();
    }
}
