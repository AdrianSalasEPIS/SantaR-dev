<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RNF-06 y RNF-09: redirige a HTTPS en producción y agrega cabeceras de seguridad
 * recomendadas por OWASP (clickjacking, tipo MIME, referer y HSTS).
 */
class CabecerasSeguridad
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('app.force_https') && ! $request->isSecure()) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
