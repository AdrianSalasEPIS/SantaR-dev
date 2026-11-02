<?php

use App\Exceptions\ReglaNegocioException;
use App\Http\Middleware\CabecerasSeguridad;
use App\Http\Middleware\CuentaActiva;
use App\Http\Middleware\VerificarRol;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Los proxies de confianza del hosting se configuran en AppServiceProvider (config/app.php).
        $middleware->append(CabecerasSeguridad::class);

        $middleware->alias([
            'rol' => VerificarRol::class,
            'activo' => CuentaActiva::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('panel'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Reglas de negocio (stock insuficiente, implementos sin salidas, etc.): volver al formulario con el mensaje.
        $exceptions->render(function (ReglaNegocioException $e, Request $request) {
            return back()->withInput()->withErrors([$e->campo => $e->getMessage()]);
        });

        // Formulario enviado con la sesión ya vencida por inactividad (RN-05).
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 419 && ! $request->expectsJson()) {
                return redirect()->route('login')->with('aviso', 'Su sesión expiró por inactividad. Ingrese nuevamente.');
            }
        });
    })->create();
