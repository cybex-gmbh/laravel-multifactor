<?php

namespace CybexGmbh\LaravelMultiFactor;

use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorChooseViewResponseContract;
use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorDeleteViewResponseContract;
use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorLoginViewResponseContract;
use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorSettingsViewResponseContract;
use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorSetupViewResponseContract;
use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorChallengeViewResponseContract;
use CybexGmbh\LaravelTwoFactor\Http\Middleware\HasAllowedTwoFactorAuthMethods;
use CybexGmbh\LaravelTwoFactor\Http\Middleware\HasTwoFactorAuthentication;
use CybexGmbh\LaravelTwoFactor\Http\Middleware\HasEmailLogin;
use CybexGmbh\LaravelTwoFactor\Http\Middleware\LimitTwoFactorAuthAccess;
use CybexGmbh\LaravelTwoFactor\Http\Middleware\RedirectIfTwoFactorAuthenticated;
use CybexGmbh\LaravelTwoFactor\View\Components\AuthCard;
use CybexGmbh\LaravelTwoFactor\View\Components\LegacyAuthCard;
use CybexGmbh\LaravelTwoFactor\View\Components\Layout;
use CybexGmbh\LaravelTwoFactor\View\Components\Svg;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MultiFactorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        $this->mergeConfigFrom(__DIR__.'/../config/two-factor.php', 'two-factor');
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-two-factor');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-two-factor');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        $router = $this->app['router'];
        $router->aliasMiddleware('hasTwoFactorAuthentication', HasTwoFactorAuthentication::class);
        $router->aliasMiddleware('hasAllowedTwoFactorAuthMethods', HasAllowedTwoFactorAuthMethods::class);
        $router->aliasMiddleware('redirectIfTwoFactorAuthenticated', RedirectIfTwoFactorAuthenticated::class);
        $router->aliasMiddleware('limitTwoFactorAuthAccess', LimitTwoFactorAuthAccess::class);
        $router->aliasMiddleware('hasEmailLogin', HasEmailLogin::class);

        Blade::component(Layout::class, 'app-layout');
        Blade::component(Svg::class, 'svg');
        Blade::component(LegacyAuthCard::class, 'legacy-auth-card');
        Blade::component(AuthCard::class, 'auth-card');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/multi-factor.php' => config_path('laravel-multi-factor.php'),
            ], ['multi-factor', 'multi-factor.config']);

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/laravel-two-factor'),
            ], ['two-factor', 'two-factor.views']);

//            $this->publishes([
//                __DIR__ . '/../resources/css/multi-factor.css' => public_path('vendor/laravel-two-factor/multi-factor.css'),
//            ], ['two-factor', 'two-factor.public']);

            $this->publishes([
                __DIR__ . '/../public' => public_path('vendor/laravel-two-factor'),
            ], ['two-factor', 'two-factor.public']);

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-multi-factor'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/laravel-multi-factor'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-multi-factor'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);
        }

        $this->app->booted(function () {
            Route::getRoutes()->refreshNameLookups();
            Route::getRoutes()->getByName(config('two-factor.routes.login.name'))->middleware('hasEmailLogin');
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Register the main class to use with the facade
        $this->app->singleton('laravel-multi-factor', function () {
            return new MultiFactor;
        });

        $this->app->singleton(MultiFactorChallengeViewResponseContract::class, fn($app, $params): MultiFactorChallengeViewResponseContract => new (config('two-factor.views.challenge'))(...$params));
        $this->app->singleton(MultiFactorLoginViewResponseContract::class, fn($app, $params): MultiFactorLoginViewResponseContract => new (config('two-factor.views.login'))(...$params));
        $this->app->singleton(MultiFactorSetupViewResponseContract::class, fn($app, $params): MultiFactorSetupViewResponseContract => new (config('two-factor.views.setup'))(...$params));
        $this->app->singleton(MultiFactorChooseViewResponseContract::class, fn($app, $params): MultiFactorChooseViewResponseContract => new (config('two-factor.views.choose'))(...$params));
        $this->app->singleton(MultiFactorDeleteViewResponseContract::class, fn($app, $params): MultiFactorDeleteViewResponseContract => new (config('two-factor.views.delete'))(...$params));
        $this->app->singleton(MultiFactorSettingsViewResponseContract::class, fn($app, $params): MultiFactorSettingsViewResponseContract => new (config('two-factor.views.settings'))(...$params));
    }
}
