<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo del módulo de inventario (SAD, Figura 17): categorías (RF-07) y productos e implementos (RF-08).
 * stock_actual solo lo modifica el servicio de inventario a partir de los movimientos (RF-14).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->unsignedSmallInteger('orden_carta')->default(1)->index();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias')->restrictOnDelete();
            $table->string('nombre', 120)->unique();
            $table->enum('tipo', ['PRODUCTO', 'IMPLEMENTO']);
            $table->string('caracteristicas', 100)->nullable();
            $table->decimal('precio', 10, 2)->default(0);
            $table->string('imagen', 255)->nullable();
            $table->unsignedInteger('stock_actual')->default(0);
            $table->unsignedInteger('stock_minimo')->default(0);
            $table->boolean('visible_en_carta')->default(true);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->index(['tipo', 'activo', 'visible_en_carta']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
        Schema::dropIfExists('categorias');
    }
};
