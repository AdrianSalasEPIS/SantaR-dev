<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** RNF-06, RNF-07, RNF-09 y RNF-10: seguridad transversal y respaldos. */
class SeguridadTest extends TestCase
{
    use RefreshDatabase;

    public function test_envia_cabeceras_de_seguridad(): void
    {
        $this->get('/login')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_en_produccion_redirige_a_https(): void
    {
        config(['app.force_https' => true]);

        $this->get('http://localhost/login')->assertRedirect('https://localhost/login');
    }

    public function test_el_panel_es_accesible_para_ambos_roles(): void
    {
        $this->actingAs($this->admin())->get('/panel')->assertOk()->assertSee('Accesos rápidos');
        $this->actingAs($this->encargado())->get('/panel')->assertOk()->assertDontSee('Accesos rápidos');
    }

    public function test_el_menu_del_encargado_no_muestra_opciones_de_administrador(): void
    {
        $this->actingAs($this->encargado())->get('/panel')
            ->assertDontSee(route('usuarios.index'))
            ->assertDontSee(route('bitacora.index'));
    }

    public function test_las_paginas_de_error_no_muestran_detalles_tecnicos(): void
    {
        $this->get('/no-existe')->assertNotFound()->assertSee('Página no encontrada');
    }

    public function test_genera_el_respaldo_diario_y_lo_registra(): void
    {
        Storage::fake('local');
        $this->admin();

        $this->artisan('santar:respaldar')->assertSuccessful();

        $archivos = Storage::disk('local')->files('backups');
        $this->assertCount(1, $archivos);
        $sql = gzdecode(Storage::disk('local')->get($archivos[0]));
        $this->assertStringContainsString('CREATE TABLE `usuarios`', $sql);
        $this->assertStringContainsString('INSERT INTO `usuarios`', $sql);
        $this->assertDatabaseHas('bitacora', ['modulo' => 'sistema', 'accion' => 'respaldo diario']);
    }

    public function test_la_copia_semanal_incluye_base_de_datos_e_imagenes(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Storage::disk('public')->put('productos/vino.jpg', 'imagen');

        $this->artisan('santar:respaldar --semanal')->assertSuccessful();

        $zips = Storage::disk('local')->files('backups/semanal');
        $this->assertCount(1, $zips);
        $zip = new \ZipArchive;
        $zip->open(Storage::disk('local')->path($zips[0]));
        $this->assertNotFalse($zip->locateName('productos/vino.jpg'));
        $zip->close();
    }
}
