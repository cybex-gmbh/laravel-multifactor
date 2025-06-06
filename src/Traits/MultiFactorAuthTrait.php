<?php

namespace CybexGmbh\LaravelMultiFactor\Traits;

use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMethod as MultiFactorAuthMethodEnum;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use CybexGmbh\LaravelMultiFactor\Models\MultiFactorAuthMethod;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;

trait MultiFactorAuthTrait
{
    /**
     * @return BelongsToMany
     */
    public function multiFactorAuthMethods(): BelongsToMany
    {
        return $this->belongsToMany(MultiFactorAuthMethod::class, 'multi_factor_auth_method_user');
    }

    /**
     * @return mixed
     */
    public function getMultiFactorAuthMethodsNames() {
        return $this->multiFactorAuthMethods->map(fn($method) => $method->type->value)->toArray();
    }

    /**
     * @return mixed
     */
    public function getMultiFactorAuthMethods() {
        return $this->multiFactorAuthMethods->pluck('type')->toArray();
    }

    /**
     * @return bool
     */
    public function hasAllowedMultiFactorAuthMethods(): bool
    {
        return filled($this->getFilteredMFAMethods());
    }

    public function getUserMethods(): array
    {
        if ($this->hasAllowedMultiFactorAuthMethods()) {
            return MultiFactorAuthMethodEnum::getMethodsByNames($this->getFilteredMFAMethods());
        } else {
            return $this->getMultiFactorAuthMethods();
        }
    }

    public function getFilteredMFAMethods(bool $onlyAllowed = true): array
    {
        $mfaMethods = $this->getMultiFactorAuthMethodsNames();

        if (MultiFactorAuthMode::isForceMode()) {
            $configuredMethods = [MultiFactorAuthMethodEnum::getForceMethod()->value];
        } else {
            $configuredMethods = MultiFactorAuthMethodEnum::getAllowedMethodsNames();
        }

        return $onlyAllowed
            ? array_intersect($mfaMethods, $configuredMethods)
            : array_diff($mfaMethods, $configuredMethods);
    }

    /**
     * @param array $allowedMethods
     * @param $userMethods
     * @return array
     */
    public function getUserMethodsWithRemainingAllowedMethods(array $allowedMethods, $userMethods): array
    {
        return array_unique(Arr::collapse([$allowedMethods, $userMethods]), SORT_REGULAR);
    }

    /**
     * @return array
     */
    public function getRemainingAllowedMethodsNames(): array {
        return array_diff(MultiFactorAuthMethodEnum::getAllowedMethodsNames(), $this->getMultiFactorAuthMethodsNames());
    }
}
