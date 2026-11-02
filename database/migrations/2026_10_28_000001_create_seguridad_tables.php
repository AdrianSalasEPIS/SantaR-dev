<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Seguridad y usuarios (SAD, Figura 17): roles, usuarios, sesiones y bitácora.
 * Las claves foráneas usan RESTRICT para impedir borrar datos con historial.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->timestamps();
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('roles')->restrictOnDelete();
            $table->string('nombre', 100);
            $table->string('correo', 150)->unique();
            $table->string('password');
            $table->boolean('activo')->default(true);
            $table->dateTime('ultimo_acceso')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Sesiones en base de datos (cierre por inactividad de 15 minutos, RN-05).
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // RF-05 / RN-20: registro automático de las acciones que modifican datos.
        Schema::create('bitacora', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->restrictOnDelete();
            $table->string('modulo', 40);
            $table->string('accion', 60);
            $table->text('detalle')->nullable();
            $table->string('ip', 45)->nullable();
            $table->dateTime('fecha_hora');
            $table->index(['usuario_id', 'fecha_hora']);
            $table->index(['modulo', 'fecha_hora']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('roles');
    }
};
