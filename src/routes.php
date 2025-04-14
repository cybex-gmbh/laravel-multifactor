<?php

use CybexGmbh\LaravelMultiFactor\Http\Controllers\MultiFactorAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::middleware(['guest'])->group(function () {
        if (config('multi-factor.routes.email-login.enabled') && $path = config('multi-factor.routes.email-login.path')) {
            Route::post($path, [MultiFactorAuthController::class, 'emailLogin'])->name('mfa.email.login');
        }
    });

    Route::middleware(['auth'])->as('mfa.')->group(function () {
        if (config('multi-factor.routes.settings.enabled') && $path = config('multi-factor.routes.settings.path')) {
            Route::middleware(['hasMultiFactorAuthentication', 'hasAllowedMultiFactorAuthMethods'])->group(function () use ($path) {
                Route::get($path, [MultiFactorAuthController::class, 'twoFactorSettings'])->name('settings');
            });
        }

        Route::prefix('mfa')->group(function () {
            Route::delete('delete/{method}', [MultiFactorAuthController::class, 'deleteTwoFactorAuthMethod'])->name('delete.method');
            Route::get('setup/{method?}', [MultiFactorAuthController::class, 'setup'])->name('setup');

            Route::middleware(['redirectIfMultiFactorAuthenticated'])->group(function () {
                Route::get('', [MultiFactorAuthController::class, 'show'])->name('show');

                Route::middleware(['limitMultiFactorAuthAccess'])->group(function () {
                    Route::get('{method}', [MultiFactorAuthController::class, 'handleTwoFactorAuthMethod'])->name('method');
                    Route::post('{method}/send', [MultiFactorAuthController::class, 'send'])->name('method.send');
                    Route::post('{method}/verify', [MultiFactorAuthController::class, 'verifyTwoFactorAuthCode'])->name('verify');
                    Route::get('{method}/login/{user}/{code}', [MultiFactorAuthController::class, 'verifyTwoFactorAuthCode'])->middleware('signed')->name('login');
                });
            });
        });
    });
});