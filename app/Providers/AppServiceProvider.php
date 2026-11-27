<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

/**
 * Proveedor principal de servicios de la aplicación.
 *
 * Centraliza configuraciones globales seguras y consistentes para todo el
 * entorno (fechas inmutables, protección de comandos destructivos y reglas
 * base de contraseñas).
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra servicios en el contenedor.
     *
     * Se deja vacío porque la aplicación no necesita bindings personalizados
     * en esta etapa.
     */
    public function register(): void
    {
        //
    }

    /**
     * Inicializa comportamientos de la aplicación al arrancar.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRateLimits();
    }

    /**
     * Los admins tienen superpoderes: no topan con los límites de escrituras.
     */
    protected function configureRateLimits(): void
    {
        $key = fn (Request $request): string => $request->ip().'|'.$request->method().'|'.$request->path();

        RateLimiter::for('social', fn (Request $request) => $request->user()?->isAdmin()
            ? Limit::none()
            : Limit::perMinute(15)->by($key($request)));

        RateLimiter::for('bible-write', fn (Request $request) => $request->user()?->isAdmin()
            ? Limit::none()
            : Limit::perMinute(30)->by($key($request)));
    }

    /**
     * Configura comportamientos por defecto para una aplicación robusta.
     *
     * - Fechas inmutables para evitar mutaciones accidentales.
     * - Bloquea comandos destructivos en producción.
     * - En producción exige contraseñas fuertes.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
