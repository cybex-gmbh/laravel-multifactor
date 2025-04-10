<?php

namespace CybexGmbh\LaravelTwoFactor\Traits;

use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthMode;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthMethod as TwoFactorAuthMethodEnum;
use CybexGmbh\LaravelTwoFactor\Models\TwoFactorAuthMethod;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait TwoFactorAuthTrait
{
    public function twoFactorAuthMethods(): BelongsToMany
    {
        return $this->belongsToMany(TwoFactorAuthMethod::class, 'two_factor_auth_method_user');
    }

    public function getTwoFactorAuthMethodsNames() {
        return $this->twoFactorAuthMethods->map(fn($method) => $method->type->value)->toArray();
    }

    public function getTwoFactorAuthMethods() {
        return $this->twoFactorAuthMethods->pluck('type')->toArray();
    }

    public function getAllowed2FAMethods(): array
    {
        $user2FAMethods = $this->getTwoFactorAuthMethodsNames();

        if (TwoFactorAuthMode::fromConfig() === TwoFactorAuthMode::FORCE) {
            $configuredMethods = [TwoFactorAuthMethodEnum::getForceMethod()->value];
        } else {
            $configuredMethods = TwoFactorAuthMethodEnum::getAllowedMethodsNames();
        }

        return array_intersect($user2FAMethods, $configuredMethods);
    }

    public function getUnallowedMethods(): array
    {
        $user2FAMethods = $this->getTwoFactorAuthMethodsNames();

        if (TwoFactorAuthMode::fromConfig() === TwoFactorAuthMode::FORCE) {
            $configuredMethods = [TwoFactorAuthMethodEnum::getForceMethod()->value];
        } else {
            $configuredMethods = TwoFactorAuthMethodEnum::getAllowedMethodsNames();
        }

        return array_diff($user2FAMethods, $configuredMethods);
    }
}