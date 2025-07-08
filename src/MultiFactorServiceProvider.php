<?php

namespace Cybex\LaravelMultiFactor;

use Cybex\LaravelMultiFactor\Contracts\MultiFactorChallengeViewResponseContract;
use Cybex\LaravelMultiFactor\Contracts\MultiFactorChooseViewResponseContract;
use Cybex\LaravelMultiFactor\Contracts\MultiFactorLoginViewResponseContract;
use Cybex\LaravelMultiFactor\Contracts\MultiFactorSettingsViewResponseContract;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Cybex\LaravelMultiFactor\Contracts\MultiFactorSetupViewResponseContract;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use Cybex\LaravelMultiFactor\Exceptions\InvalidEmailOnlyLoginConfigurationException;
use Cybex\LaravelMultiFactor\Exceptions\LoginRouteNotFoundException;
use Cybex\LaravelMultiFactor\Facades\MFA;
use Cybex\LaravelMultiFactor\Helpers\MFAHelper;
use Cybex\LaravelMultiFactor\Http\Middleware\EnforceEmailOnlyLogin;
use Cybex\LaravelMultiFactor\Http\Middleware\HasAllowedMultiFactorAuthMethods;
use Cybex\LaravelMultiFactor\Http\Middleware\HasMultiFactorAuthentication;
use Cybex\LaravelMultiFactor\Http\Middleware\LimitMultiFactorAuthAccess;
use Cybex\LaravelMultiFactor\Http\Middleware\RedirectIfInSetup;
use Cybex\LaravelMultiFactor\Http\Middleware\RedirectIfMultiFactorAuthenticated;
use Cybex\LaravelMultiFactor\Listeners\HandleUserLogout;
use Cybex\LaravelMultiFactor\Providers\FortifyServiceProvider;
use Cybex\LaravelMultiFactor\View\Components\LegacyAuthCard;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Events\ValidTwoFactorAuthenticationCodeProvided;
use Cybex\LaravelMultiFactor\Listeners\HandleFortifyTOTPLogin;

class MultiFactorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        $this->mergeConfigFrom(__DIR__ . '/../config/multi-factor.php', 'multi-factor');
        $this->mergeConfigFrom(__DIR__ . '/../config/fortify.php', 'fortify');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'multi-factor');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laravel-multi-factor');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        $router = $this->app['router'];
        $router->aliasMiddleware('hasMultiFactorAuthentication', HasMultiFactorAuthentication::class);
        $router->aliasMiddleware('hasAllowedMultiFactorAuthMethods', HasAllowedMultiFactorAuthMethods::class);
        $router->aliasMiddleware('redirectIfMultiFactorAuthenticated', RedirectIfMultiFactorAuthenticated::class);
        $router->aliasMiddleware('limitMultiFactorAuthAccess', LimitMultiFactorAuthAccess::class);
        $router->aliasMiddleware('enforceEmailOnlyLogin', EnforceEmailOnlyLogin::class);

        $router->middlewareGroup('mfa', [
            'hasMultiFactorAuthentication',
            'hasAllowedMultiFactorAuthMethods',
        ]);

        Blade::componentNamespace('Cybex\\LaravelMultiFactor\\View\\Components', 'multi-factor');

        Event::listen(Logout::class, HandleUserLogout::class);
        Event::listen(ValidTwoFactorAuthenticationCodeProvided::class, HandleFortifyTOTPLogin::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/multi-factor.php' => config_path('multi-factor.php'),
            ], ['multi-factor', 'multi-factor.config']);

            $this->publishes([
                __DIR__ . '/../resources/views/pages' => resource_path('views/vendor/laravel-multi-factor/pages'),
            ], ['multi-factor', 'multi-factor.views']);

            $this->publishes([
                __DIR__ . '/../public' => public_path('vendor/laravel-multi-factor'),
            ], ['multi-factor', 'multi-factor.public']);

            $this->publishes([
                __DIR__ . '/../resources/lang' => resource_path('lang/vendor/laravel-multi-factor'),
            ], ['multi-factor', 'multi-factor.lang']);
        }

        if (MultiFactorAuthMode::isForceMode()) {
            $forceMethod = MFA::getForceMethod();

            if (!$forceMethod->isAllowed()) {
                abort(500);
            }
        }

        $this->app->booted(function () {
            $routes = Route::getRoutes();

            $routes->refreshNameLookups();
            $loginRoute = $routes->getByName(config('multi-factor.features.email-login.applicationLoginRouteName'));

            if (MFA::isEmailOnlyLoginActive()) {
                if (!$loginRoute) {
                    throw new LoginRouteNotFoundException();
                }

                if (!MultiFactorAuthMode::isForceMode() || MFA::getForceMethod() !== MultiFactorAuthMethod::EMAIL) {
                    throw new InvalidEmailOnlyLoginConfigurationException();
                }

                $loginRoute->middleware('enforceEmailOnlyLogin');
            }
        });
    }

    public function register()
    {
        // Register the main class to use with the facade
        $this->app->singleton('mfa', MFAHelper::class);

        $this->app->booting(function () {
            AliasLoader::getInstance()->alias('MFA', MFA::class);
        });

        $this->app->register(FortifyServiceProvider::class);

        $this->app->singleton(
            MultiFactorChallengeViewResponseContract::class,
            fn($app, $params): MultiFactorChallengeViewResponseContract => new (config('multi-factor.views.challenge'))(...$params)
        );
        $this->app->singleton(
            MultiFactorLoginViewResponseContract::class,
            fn($app, $params): MultiFactorLoginViewResponseContract => new (config('multi-factor.views.login'))(...$params)
        );
        $this->app->singleton(
            MultiFactorChooseViewResponseContract::class,
            fn($app, $params): MultiFactorChooseViewResponseContract => new (config('multi-factor.views.choose'))($params)
        );
        $this->app->singleton(
            MultiFactorSettingsViewResponseContract::class,
            fn($app, $params): MultiFactorSettingsViewResponseContract => new (config('multi-factor.views.settings'))(...$params)
        );
        $this->app->singleton(
            MultiFactorSetupViewResponseContract::class,
            fn($app, $params): MultiFactorSetupViewResponseContract => new (config('multi-factor.views.setup'))(...$params)
        );
    }
}
