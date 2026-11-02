<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** RF-04 Cambiar contraseña (CU-04, RN-04). */
class PerfilTest extends TestCase
{
    use RefreshDatabase;

    public function test_muestra_la_pantalla_mi_cuenta_a_cualquier_rol(): void
    {
        $this->actingAs($this->encargado())->get('/mi-cuenta')->assertOk()->assertSee('Cambiar contraseña');
    }

    public function test_cambia_la_contrasena_validando_la_actual(): void
    {
        $usuario = $this->encargado();

        $this->actingAs($usuario)->put('/mi-cuenta/contrasena', [
            'password_actual' => self::PASSWORD,
            'password' => 'NuevaClave2026',
            'password_confirmation' => 'NuevaClave2026',
        ])->assertSessionHas('exito');

        $this->assertTrue(Hash::check('NuevaClave2026', $usuario->fresh()->password));
        $this->assertDatabaseHas('bitacora', ['usuario_id' => $usuario->id, 'accion' => 'cambio de contraseña']);
    }

    public function test_rechaza_si_la_contrasena_actual_es_incorrecta(): void
    {
        $usuario = $this->encargado();

        $this->actingAs($usuario)->put('/mi-cuenta/contrasena', [
            'password_actual' => 'Equivocada1',
            'password' => 'NuevaClave2026',
            'password_confirmation' => 'NuevaClave2026',
        ])->assertSessionHasErrors('password_actual');

        $this->assertTrue(Hash::check(self::PASSWORD, $usuario->fresh()->password));
    }

    public function test_rechaza_si_las_nuevas_no_coinciden_o_son_debiles(): void
    {
        $usuario = $this->encargado();

        $this->actingAs($usuario)->put('/mi-cuenta/contrasena', [
            'password_actual' => self::PASSWORD, 'password' => 'NuevaClave2026', 'password_confirmation' => 'Distinta2026',
        ])->assertSessionHasErrors('password');

        $this->actingAs($usuario)->put('/mi-cuenta/contrasena', [
            'password_actual' => self::PASSWORD, 'password' => 'debil', 'password_confirmation' => 'debil',
        ])->assertSessionHasErrors('password');
    }
}
