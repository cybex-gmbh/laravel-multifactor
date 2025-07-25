<?php

namespace Cybex\LaravelMultiFactor\Traits;

use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use Cybex\LaravelMultiFactor\Models\MultiFactorAuthMethod;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use MFA;

trait MultiFactorAuthTrait
{
    public function multiFactorAuthMethods(): BelongsToMany
    {
        return $this->belongsToMany(MultiFactorAuthMethod::class, 'multi_factor_auth_method_user');
    }

    public function getMultiFactorAuthMethodNames()
    {
        return $this->multiFactorAuthMethods->map(fn($method) => $method->type->value)->toArray();
    }

    public function getMultiFactorAuthMethods()
    {
        return $this->multiFactorAuthMethods->pluck('type')->toArray();
    }

    public function hasAllowedMultiFactorAuthMethods(): bool
    {
        return filled($this->getAllowedMultiFactorAuthMethods());
    }

    /**
     * Returns the user's allowed MFA methods if available, otherwise all user methods.
     *
     * @return array
     */
    public function getUserMethods(): array
    {
        if ($this->hasAllowedMultiFactorAuthMethods()) {
            return MFA::getMethodsByNames($this->getAllowedMultiFactorAuthMethods());
        } else {
            return $this->getMultiFactorAuthMethods();
        }
    }

    public function getAllowedMultiFactorAuthMethods(): array
    {
        return array_intersect($this->getMultiFactorAuthMethodNames(), $this->getConfiguredMultiFactorAuthMethodNames());
    }

    public function getUnallowedMultiFactorAuthMethods(): array
    {
        return array_diff($this->getMultiFactorAuthMethodNames(), $this->getConfiguredMultiFactorAuthMethodNames());
    }

    public function getConfiguredMultiFactorAuthMethodNames(): array
    {
        if (MultiFactorAuthMode::isForceMode()) {
            return [MFA::getForceMethod()->value];
        } else {
            return MFA::getAllowedMethodNames();
        }
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
