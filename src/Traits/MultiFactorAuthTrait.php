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

    /**
     * Returns the user's allowed MFA methods if available, otherwise all user methods.
     *
     * @return array
     */
    public function getUserMethods(): array
    {
        if ($this->hasAllowedMultiFactorAuthMethods()) {
            return MultiFactorAuthMethodEnum::getMethodsByNames($this->getFilteredMFAMethods());
        } else {
            return $this->getMultiFactorAuthMethods();
        }
    }

    /**
     * Returns the user's allowed or unallowed MFA methods.
     *
     * @param bool $onlyAllowed
     * @return array
     */
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
     * Returns a combined list of user-configured methods and the remaining allowed methods.
     *
     * @param array $allowedMethods
     * @param $userMethods
     * @return array
     */
    public function getUserMethodsWithRemainingAllowedMethods(array $allowedMethods, $userMethods): array
    {
        return array_unique(Arr::collapse([$allowedMethods, $userMethods]), SORT_REGULAR);
    }
}
