<?php

use Illuminate\Support\Facades\Route;
use CybexGmbh\LaravelTwoFactor\Http\Controllers\TwoFactorAuthController;

Route::middleware(['web'])->group(function () {
    Route::middleware(['guest'])->group(function () {
        if (config('two-factor.routes.email-login.enabled') && $path = config('two-factor.routes.email-login.path')) {
            Route::post($path, [TwoFactorAuthController::class, 'emailLogin'])->name('2fa.email.login');
        }
    });

    Route::middleware(['auth'])->as('2fa.')->group(function () {
        if (config('two-factor.routes.settings.enabled') && $path = config('two-factor.routes.settings.path')) {
            Route::middleware(['hasTwoFactorAuthentication', 'hasAllowedTwoFactorAuthMethods'])->group(function () use ($path) {
                Route::get($path, [TwoFactorAuthController::class, 'twoFactorSettings'])->name('settings');
            });
        }

        Route::prefix('2fa')->group(function () {
            Route::delete('delete/{method}', [TwoFactorAuthController::class, 'deleteTwoFactorAuthMethod'])->name('delete.method');
            Route::get('setup/{method?}', [TwoFactorAuthController::class, 'setup'])->name('setup');

            Route::middleware(['redirectIfTwoFactorAuthenticated'])->group(function () {
                Route::get('', [TwoFactorAuthController::class, 'show'])->name('show');

                Route::middleware(['limitTwoFactorAuthAccess'])->group(function () {
                    Route::get('{method}', [TwoFactorAuthController::class, 'handleTwoFactorAuthMethod'])->name('method');
                    Route::post('{method}/send', [TwoFactorAuthController::class, 'send'])->name('method.send');
                    Route::post('{method}/verify', [TwoFactorAuthController::class, 'verifyTwoFactorAuthCode'])->name('verify');
                    Route::get('{method}/login/{user}/{code}', [TwoFactorAuthController::class, 'verifyTwoFactorAuthCode'])->middleware('signed')->name('login');
                });
            });
        });
    });
});