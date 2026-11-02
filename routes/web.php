<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\UsuarioController;
use App\Models\Rol;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/panel');

// CU-01 / CU-02 · No existe ruta de registro: solo el administrador crea cuentas (RN-01).
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'mostrar'])->name('login');
    // Además del bloqueo por cuenta (RN-05), se limita el total de intentos por IP.
    Route::post('/login', [LoginController::class, 'ingresar'])->middleware('throttle:30,1')->name('login.ingresar');
});
Route::post('/logout', [LoginController::class, 'salir'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'activo'])->group(function () {
    Route::get('/panel', [PanelController::class, 'index'])->name('panel');
    Route::get('/mi-cuenta', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::put('/mi-cuenta/contrasena', [PerfilController::class, 'actualizarContrasena'])->name('perfil.contrasena');

    // Solo Administrador (RN-02).
    Route::middleware('rol:'.Rol::ADMINISTRADOR)->group(function () {
        Route::resource('usuarios', UsuarioController::class)->except(['show', 'destroy']);
        Route::patch('/usuarios/{usuario}/estado', [UsuarioController::class, 'cambiarEstado'])->name('usuarios.estado');

        Route::get('/bitacora', [BitacoraController::class, 'index'])->name('bitacora.index');

        Route::resource('categorias', CategoriaController::class)->except(['show', 'destroy'])
            ->parameters(['categorias' => 'categoria']);
        Route::patch('/categorias/{categoria}/estado', [CategoriaController::class, 'cambiarEstado'])->name('categorias.estado');

        Route::resource('productos', ProductoController::class)->except(['show', 'destroy']);
        Route::patch('/productos/{producto}/estado', [ProductoController::class, 'cambiarEstado'])->name('productos.estado');
    });
});
