<?php

use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Cybex\LaravelMultiFactor\Http\Controllers\MultiFactorAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::middleware(['guest'])->group(function () {
        if (MultiFactorAuthMethod::isEmailOnlyLoginActive() && $path = config('multi-factor.features.email-login.routePath')) {
            Route::post($path, [MultiFactorAuthController::class, 'authenticateByEmailOnly'])->name('mfa.email.login');
        }
    });

    Route::middleware(['auth'])->as('mfa.')->group(function () {
        if ($path = config('multi-factor.features.settings.routePath')) {
            Route::middleware(['hasMultiFactorAuthentication', 'hasAllowedMultiFactorAuthMethods'])->group(function () use ($path) {
                Route::get($path, [MultiFactorAuthController::class, 'multiFactorSettings'])->name('settings');
            });
        }

        Route::prefix('mfa')->group(function () {
            Route::delete('delete/{method}', [MultiFactorAuthController::class, 'deleteMultiFactorAuthMethod'])->name('delete.method');
            Route::get('setup/{method?}', [MultiFactorAuthController::class, 'setup'])->name('setup');

            Route::middleware(['redirectIfMultiFactorAuthenticated'])->group(function () {
                Route::get('', [MultiFactorAuthController::class, 'show'])->name('show');

                Route::middleware(['limitMultiFactorAuthAccess'])->group(function () {
                    Route::get('{method}', [MultiFactorAuthController::class, 'handleMultiFactorAuthMethod'])->name('method');
                    Route::post('{method}/send', [MultiFactorAuthController::class, 'send'])->name('method.send');
                    Route::post('{method}/verify', [MultiFactorAuthController::class, 'verifyTwoFactorAuthCode'])->name('verify');
                    Route::get('{method}/login/{user}/{code}', [MultiFactorAuthController::class, 'verifyTwoFactorAuthCode'])->middleware('signed')->name('login');
                });
            });
        });
    });
});
