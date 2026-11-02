<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** RF-03 Gestionar usuarios (CU-03, RN-01 a RN-04). */
class UsuarioTest extends TestCase
{
    use RefreshDatabase;

    private function datos(array $cambios = []): array
    {
        return $cambios + [
            'nombre' => 'María Quispe',
            'correo' => 'maria@santar.test',
            'rol_id' => $this->rol(Rol::ENCARGADO)->id,
            'password' => 'Temporal2026',
            'password_confirmation' => 'Temporal2026',
        ];
    }

    public function test_el_administrador_crea_una_cuenta_con_contrasena_cifrada(): void
    {
        $this->actingAs($this->admin())->post('/usuarios', $this->datos(['correo' => 'MARIA@Santar.test']))
            ->assertRedirect('/usuarios');

        $usuario = Usuario::where('correo', 'maria@santar.test')->firstOrFail();
        $this->assertNotSame('Temporal2026', $usuario->password);
        $this->assertTrue(Hash::check('Temporal2026', $usuario->password));
        $this->assertTrue($usuario->activo);
        $this->assertDatabaseHas('bitacora', ['modulo' => 'usuarios', 'accion' => 'usuario creado']);
    }

    public function test_no_permite_correos_repetidos(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/usuarios', $this->datos(['correo' => $admin->correo]))
            ->assertSessionHasErrors('correo');
    }

    public function test_exige_contrasena_de_8_caracteres_con_letras_y_numeros(): void
    {
        $admin = $this->admin();

        foreach (['corta1', 'sololetras', '12345678'] as $clave) {
            $this->actingAs($admin)->post('/usuarios', $this->datos(['password' => $clave, 'password_confirmation' => $clave]))
                ->assertSessionHasErrors('password');
        }
        $this->assertDatabaseMissing('usuarios', ['correo' => 'maria@santar.test']);
    }

    public function test_el_encargado_de_sede_no_puede_gestionar_usuarios(): void
    {
        $encargado = $this->encargado();

        $this->actingAs($encargado)->get('/usuarios')->assertForbidden();
        $this->actingAs($encargado)->post('/usuarios', $this->datos())->assertForbidden();
    }

    public function test_edita_datos_sin_cambiar_la_contrasena_si_se_deja_vacia(): void
    {
        $admin = $this->admin();
        $otro = $this->encargado();
        $hashAnterior = $otro->password;

        $this->actingAs($admin)->put("/usuarios/{$otro->id}", [
            'nombre' => 'Nombre Editado', 'correo' => $otro->correo, 'rol_id' => $otro->rol_id, 'password' => '',
        ])->assertRedirect('/usuarios');

        $this->assertSame('Nombre Editado', $otro->fresh()->nombre);
        $this->assertSame($hashAnterior, $otro->fresh()->password);
    }

    public function test_el_administrador_no_puede_cambiar_su_propio_rol(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put("/usuarios/{$admin->id}", [
            'nombre' => $admin->nombre, 'correo' => $admin->correo, 'rol_id' => $this->rol(Rol::ENCARGADO)->id,
        ])->assertSessionHasErrors('rol_id');

        $this->assertTrue($admin->fresh()->esAdministrador());
    }

    public function test_desactiva_y_reactiva_una_cuenta_conservando_su_historial(): void
    {
        $admin = $this->admin();
        $otro = $this->encargado();

        $this->actingAs($admin)->patch("/usuarios/{$otro->id}/estado")->assertSessionHas('exito');
        $this->assertFalse($otro->fresh()->activo);
        $this->assertDatabaseHas('usuarios', ['id' => $otro->id]); // no se elimina (RN-03)

        $this->actingAs($admin)->patch("/usuarios/{$otro->id}/estado");
        $this->assertTrue($otro->fresh()->activo);
    }

    public function test_no_puede_desactivar_su_propia_cuenta(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->patch("/usuarios/{$admin->id}/estado")->assertSessionHas('error');
        $this->assertTrue($admin->fresh()->activo);
    }

    public function test_no_existe_la_opcion_de_eliminar_usuarios(): void
    {
        $admin = $this->admin();
        $otro = $this->encargado();

        $this->actingAs($admin)->delete("/usuarios/{$otro->id}")->assertStatus(405);
    }
}
