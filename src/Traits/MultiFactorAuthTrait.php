<?php

namespace CybexGmbh\LaravelMultiFactor\Traits;

use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMethod as MultiFactorAuthMethodEnum;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use CybexGmbh\LaravelMultiFactor\Models\MultiFactorAuthMethod;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait MultiFactorAuthTrait
{
    public function multiFactorAuthMethods(): BelongsToMany
    {
        return $this->belongsToMany(MultiFactorAuthMethod::class, 'multi_factor_auth_method_user');
    }

    public function getMultiFactorAuthMethodsNames() {
        return $this->multiFactorAuthMethods->map(fn($method) => $method->type->value)->toArray();
    }

    public function getMultiFactorAuthMethods() {
        return $this->multiFactorAuthMethods->pluck('type')->toArray();
    }

    public function getAllowed2FAMethods(): array
    {
        $user2FAMethods = $this->getMultiFactorAuthMethodsNames();

        if (MultiFactorAuthMode::fromConfig() === MultiFactorAuthMode::FORCE) {
            $configuredMethods = [MultiFactorAuthMethodEnum::getForceMethod()->value];
        } else {
            $configuredMethods = MultiFactorAuthMethodEnum::getAllowedMethodsNames();
        }

        return array_intersect($user2FAMethods, $configuredMethods);
    }

    public function getUnallowedMethodsNames(): array
    {
        $user2FAMethods = $this->getMultiFactorAuthMethodsNames();

        if (MultiFactorAuthMode::fromConfig() === MultiFactorAuthMode::FORCE) {
            $configuredMethods = [MultiFactorAuthMethodEnum::getForceMethod()->value];
        } else {
            $configuredMethods = MultiFactorAuthMethodEnum::getAllowedMethodsNames();
        }

        return array_diff($user2FAMethods, $configuredMethods);
    }

    /**
     * @param array $allowedMethods
     * @param $userMethods
     * @return array
     */
    public function getUserMethodsWithRemainingAllowedMethods(array $allowedMethods, $userMethods): array
    {
        $methods = array_filter($allowedMethods, function ($method) use ($userMethods) {
            return in_array($method, $userMethods) || !in_array($method, $userMethods);
        });

        return $methods;
    }

    public function getRemainingAllowedMethodsNames(): array {
        return array_diff(MultiFactorAuthMethodEnum::getAllowedMethodsNames(), $this->getMultiFactorAuthMethodsNames());
    }
}
