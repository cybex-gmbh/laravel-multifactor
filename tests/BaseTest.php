<?php

namespace Cybex\LaravelMultiFactor\Tests;

use App\Models\User;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Cybex\LaravelMultiFactor\Models\MultiFactorAuthMethod as MultiFactorAuthMethodModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\TwoFactorAuthenticationProvider;
use Tests\TestCase;

abstract class BaseTest extends TestCase
{
    use RefreshDatabase;

    public function configureMFA(string $mode = 'optional', array $allowedMethods = ['email', 'totp'], string $forceMethod = 'email'): void
    {
        config()->set([
            'multi-factor.mode' => $mode,
            'multi-factor.allowedMethods' => $allowedMethods,
            'multi-factor.forceMethod' => $forceMethod,
        ]);
    }

    public function makeUser(MultiFactorAuthMethod ...$methods): User
    {
        $attributes = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ];

        $user = User::make($attributes);

        $user->saveQuietly();

        if ($methods) {
            $this->addMFAMethodsToUser($user, ...$methods);
        }

        return $user;
    }

    public function addMFAMethodsToUser(User $user, MultiFactorAuthMethod ...$methods): void
    {
        if (in_array(MultiFactorAuthMethod::TOTP, $methods)) {
            $provider = app(TwoFactorAuthenticationProvider::class);
            $secret = $provider->generateSecretKey();

            $totpAttributes['two_factor_secret'] = encrypt($secret);
            $totpAttributes['two_factor_confirmed_at'] = now();
        }

        if (isset($totpAttributes)) {
            $user->two_factor_secret = $totpAttributes['two_factor_secret'];
            $user->two_factor_confirmed_at = $totpAttributes['two_factor_confirmed_at'];
            $user->saveQuietly();
        }

        foreach ($methods as $method) {
            $user->multiFactorAuthMethods()->attach(MultiFactorAuthMethodModel::firstOrCreate([
                'type' => $method,
            ]));
        }
    }
}