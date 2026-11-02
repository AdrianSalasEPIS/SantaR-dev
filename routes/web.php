<?php

use App\Http\Controllers\AbastecimientoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ExistenciaController;
use App\Http\Controllers\IncidenciaController;
use App\Http\Controllers\MovimientoController;
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

    // Operación diaria del inventario: Administrador y Encargado de sede (Tabla 9 del SRS).
    Route::middleware('rol:'.Rol::ADMINISTRADOR.','.Rol::ENCARGADO)->group(function () {
        Route::get('/existencias', [ExistenciaController::class, 'index'])->name('existencias.index');

        Route::get('/entradas/registrar', [MovimientoController::class, 'crearEntrada'])->name('movimientos.entrada');
        Route::post('/entradas', [MovimientoController::class, 'guardarEntrada'])->name('movimientos.entrada.guardar');
        Route::get('/salidas/registrar', [MovimientoController::class, 'crearSalida'])->name('movimientos.salida');
        Route::post('/salidas', [MovimientoController::class, 'guardarSalida'])->name('movimientos.salida.guardar');

        Route::resource('abastecimientos', AbastecimientoController::class)->only(['index', 'create', 'store', 'show']);
        Route::resource('incidencias', IncidenciaController::class)->only(['index', 'create', 'store']);
    });

    // Solo Administrador (RN-02).
    Route::middleware('rol:'.Rol::ADMINISTRADOR)->group(function () {
        // RF-11: solo el administrador registra ajustes.
        Route::get('/ajustes/registrar', [MovimientoController::class, 'crearAjuste'])->name('movimientos.ajuste');
        Route::post('/ajustes', [MovimientoController::class, 'guardarAjuste'])->name('movimientos.ajuste.guardar');

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
