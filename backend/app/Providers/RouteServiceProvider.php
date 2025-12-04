<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            // Rutas API principales
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Rutas Web
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Cargar rutas de módulos automáticamente
            $this->loadModuleRoutes();
        });
    }

    /**
     * Cargar rutas de todos los módulos
     */
    protected function loadModuleRoutes(): void
    {
        $modulesPath = app_path('Modules');

        if (!File::exists($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);

        foreach ($modules as $modulePath) {
            $moduleName = basename($modulePath);
            
            // Saltar el módulo Shared
            if ($moduleName === 'Shared') {
                continue;
            }

            $routeFile = "$modulePath/Routes/api.php";

            if (File::exists($routeFile)) {
                Route::middleware('api')
                    ->prefix('api/' . strtolower($moduleName))
                    ->name(strtolower($moduleName) . '.')
                    ->group($routeFile);
            }
        }
    }
}
