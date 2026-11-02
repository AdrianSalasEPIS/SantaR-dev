<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Módulo de inventario (SAD, Figura 17 y Tabla 8): abastecimientos, su detalle, incidencias y movimientos.
 * Las claves foráneas usan RESTRICT para impedir borrar datos con historial.
 */
return new class extends Migration
{
    public function up(): void
    {
        // RF-12: cabecera de lo recibido de la bodega principal.
        Schema::create('abastecimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete();
            $table->date('fecha_recepcion');
            $table->string('nro_guia', 30)->nullable();
            $table->string('observacion', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('detalle_abastecimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('abastecimiento_id')->constrained('abastecimientos')->restrictOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->unsignedInteger('cantidad');
            $table->unique(['abastecimiento_id', 'producto_id']); // RN-12: un producto repetido suma su cantidad
        });

        // RF-13: pérdidas, roturas y otros eventos.
        Schema::create('incidencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete();
            $table->enum('tipo', ['PERDIDA', 'ROTURA', 'OTRO']);
            $table->unsignedInteger('cantidad');
            $table->string('descripcion', 255);
            $table->dateTime('fecha_hora');
            $table->index(['producto_id', 'fecha_hora']);
        });

        // RF-14 / RN-09: historial inmutable de cambios de stock (sin updated_at ni borrado).
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('abastecimiento_id')->nullable()->constrained('abastecimientos')->restrictOnDelete();
            $table->foreignId('incidencia_id')->nullable()->unique()->constrained('incidencias')->restrictOnDelete();
            $table->enum('tipo', ['ENTRADA', 'SALIDA', 'AJUSTE', 'ABASTECIMIENTO', 'INCIDENCIA']);
            $table->string('motivo', 30);
            $table->unsignedInteger('cantidad');
            $table->unsignedInteger('stock_resultante');
            $table->string('observacion', 255)->nullable();
            $table->dateTime('fecha_hora');
            $table->index(['producto_id', 'fecha_hora']);
            $table->index('fecha_hora');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos');
        Schema::dropIfExists('incidencias');
        Schema::dropIfExists('detalle_abastecimientos');
        Schema::dropIfExists('abastecimientos');
    }
};
