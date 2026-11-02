<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Carbon::setLocale('es');

        // RN-04: al menos 8 caracteres, con letras y números.
        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        // Durante el desarrollo se detectan consultas N+1 (rendimiento, RNF-03).
        Model::preventLazyLoading(! $this->app->isProduction());

        // Producción en el hosting: tráfico detrás del proxy del proveedor y enlaces siempre https (RNF-06).
        if ($proxies = config('app.trusted_proxies')) {
            TrustProxies::at($proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)));
        }
        if (config('app.force_https')) {
            URL::forceScheme('https');
        }
    }
}
