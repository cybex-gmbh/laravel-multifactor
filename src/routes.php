<?php

use Illuminate\Support\Facades\Route;
use CybexGmbh\LaravelTwoFactor\Http\Controllers\TwoFactorAuthController;

Route::middleware(['web'])->group(function () {
    Route::middleware(['guest'])->group(function () {
        if (config('two-factor.routes.email-login.enabled') && $path = config('two-factor.routes.email-login.path')) {
            Route::post($path, [TwoFactorAuthController::class, 'emailLogin'])->name('2fa.email.login');
        }
    });

    Route::middleware(['auth'])->group(function () {
        if (config('two-factor.routes.settings.enabled') && $path = config('two-factor.routes.settings.path')) {
            Route::get($path, [TwoFactorAuthController::class, 'twoFactorSettings'])->name('2fa.settings');
        }

        Route::get('two-factor-auth/delete', [TwoFactorAuthController::class, 'handleDeletion'])->name('2fa.delete');
        Route::delete('two-factor-auth/delete/{method}', [TwoFactorAuthController::class, 'deleteTwoFactorAuthMethod'])->name('2fa.delete.method');

        Route::middleware(['redirectIfTwoFactorAuthenticated'])->group(function () {
            Route::get('2fa', [TwoFactorAuthController::class, 'show'])->name('2fa.show');
            Route::get('two-factor-auth/setup', [TwoFactorAuthController::class, 'setup'])->name('2fa.setup');
            Route::get('two-factor-auth/setup/{method}', [TwoFactorAuthController::class, 'handleTwoFactorAuthSetup'])->name('2fa.setup.method');

            Route::middleware(['limitTwoFactorAuthAccess'])->group(function () {
                Route::get('2fa/{method}', [TwoFactorAuthController::class, 'handleTwoFactorAuthMethod'])->name('2fa.method');
                Route::post('2fa/{method}/send', [TwoFactorAuthController::class, 'send'])->name('2fa.method.send');
                Route::post('2fa/{method}/verify', [TwoFactorAuthController::class, 'verifyTwoFactorAuthCode'])->name('2fa.verify');
                Route::get('2fa/{method}/login/{user}/{code}', [TwoFactorAuthController::class, 'verifyTwoFactorAuthCode'])->middleware('signed')->name('2fa.login');
            });
        });
    });
});