<?php

namespace CybexGmbh\LaravelMultiFactor;

use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorChallengeViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorChooseViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorDeleteViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorLoginViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorSettingsViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Contracts\MultiFactorSetupViewResponseContract;
use CybexGmbh\LaravelMultiFactor\Http\Middleware\EnforceEmailOnlyLogin;
use CybexGmbh\LaravelMultiFactor\Http\Middleware\HasAllowedMultiFactorAuthMethods;
use CybexGmbh\LaravelMultiFactor\Http\Middleware\HasMultiFactorAuthentication;
use CybexGmbh\LaravelMultiFactor\Http\Middleware\LimitMultiFactorAuthAccess;
use CybexGmbh\LaravelMultiFactor\Http\Middleware\RedirectIfInSetup;
use CybexGmbh\LaravelMultiFactor\Http\Middleware\RedirectIfMultiFactorAuthenticated;
use CybexGmbh\LaravelMultiFactor\Listeners\HandleUserLogout;
use CybexGmbh\LaravelMultiFactor\View\Components\AuthCard;
use CybexGmbh\LaravelMultiFactor\View\Components\Form\Input;
use CybexGmbh\LaravelMultiFactor\View\Components\Layout;
use CybexGmbh\LaravelMultiFactor\View\Components\LegacyAuthCard;
use CybexGmbh\LaravelMultiFactor\View\Components\Svg;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
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
        $this->mergeConfigFrom(__DIR__ . '/../config/multi-factor.php', 'multi-factor');
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-multi-factor');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-multi-factor');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        $router = $this->app['router'];
        $router->aliasMiddleware('hasMultiFactorAuthentication', HasMultiFactorAuthentication::class);
        $router->aliasMiddleware('hasAllowedMultiFactorAuthMethods', HasAllowedMultiFactorAuthMethods::class);
        $router->aliasMiddleware('redirectIfMultiFactorAuthenticated', RedirectIfMultiFactorAuthenticated::class);
        $router->aliasMiddleware('limitMultiFactorAuthAccess', LimitMultiFactorAuthAccess::class);
        $router->aliasMiddleware('enforceEmailOnlyLogin', EnforceEmailOnlyLogin::class);

        Blade::component(Layout::class, 'multi-factor-layout');
        Blade::component(Svg::class, 'svg');
        Blade::component(AuthCard::class, 'multi-factor-auth-card');
        Blade::component(Input::class, 'input');

        Event::listen(Logout::class, HandleUserLogout::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/multi-factor.php' => config_path('multi-factor.php'),
            ], ['multi-factor', 'multi-factor.config']);

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/laravel-multi-factor'),
            ], ['multi-factor', 'multi-factor.views']);

            $this->publishes([
                __DIR__ . '/../public' => public_path('vendor/laravel-multi-factor'),
            ], ['multi-factor', 'multi-factor.public']);

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-multi-factor'),
            ], 'lang');*/
        }

        $this->app->booted(function () {
            $routes = Route::getRoutes();

            $routes->refreshNameLookups();
            $routes->getByName(config('multi-factor.features.email-login.applicationLoginRouteName'))->middleware('enforceEmailOnlyLogin');
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

        $this->app->singleton(MultiFactorChallengeViewResponseContract::class, fn($app, $params): MultiFactorChallengeViewResponseContract => new (config('multi-factor.views.challenge'))(...$params));
        $this->app->singleton(MultiFactorLoginViewResponseContract::class, fn($app, $params): MultiFactorLoginViewResponseContract => new (config('multi-factor.views.login'))(...$params));
        $this->app->singleton(MultiFactorSetupViewResponseContract::class, fn($app, $params): MultiFactorSetupViewResponseContract => new (config('multi-factor.views.setup'))($params));
        $this->app->singleton(MultiFactorChooseViewResponseContract::class, fn($app, $params): MultiFactorChooseViewResponseContract => new (config('multi-factor.views.choose'))($params));
        $this->app->singleton(MultiFactorDeleteViewResponseContract::class, fn($app, $params): MultiFactorDeleteViewResponseContract => new (config('multi-factor.views.delete'))(...$params));
        $this->app->singleton(MultiFactorSettingsViewResponseContract::class, fn($app, $params): MultiFactorSettingsViewResponseContract => new (config('multi-factor.views.settings'))(...$params));
    }
}
