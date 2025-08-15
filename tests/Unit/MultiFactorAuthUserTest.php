<?php

namespace Cybex\LaravelMultiFactor\Tests\Unit;

use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use Cybex\LaravelMultiFactor\Http\Controllers\MultiFactorAuthController;
use Cybex\LaravelMultiFactor\Tests\BaseTest;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\DataProvider;

class MultiFactorAuthUserTest extends BaseTest
{
    #[DataProvider('deleteMfaMethodProvider')]
    public function testDeletesUsersMfaMethod($allowedMethods, $userMethods, $methodToDelete)
    {
        $this->configureMFA(allowedMethods: $allowedMethods);

        $user = $this->makeUser($userMethods);

        $this->actingAs($user);

        app(MultiFactorAuthController::class)->deleteMultiFactorAuthMethod($methodToDelete);

        if ($methodToDelete === MultiFactorAuthMethod::TOTP) {
            $this->assertNull($user->two_factor_secret);
            $this->assertNull($user->two_factor_confirmed_at);
        }

        $this->assertUserDoesNotHaveMethod($user, $methodToDelete);

        foreach (Arr::where($userMethods, fn($method) => $method !== $methodToDelete) as $method) {
            $this->assertUserHasMethod($user, $method);
        }
    }

    public static function deleteMfaMethodProvider()
    {
        return [
            'user_has_email_and_totp_and_deletes_email' => [
                'allowedMethods' => [MultiFactorAuthMethod::EMAIL->value],
                'userMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
                'methodToDelete' => MultiFactorAuthMethod::EMAIL,
            ],
            'user_has_totp_and_deletes_totp' => [
                'allowedMethods' => [MultiFactorAuthMethod::TOTP->value],
                'userMethods' => [MultiFactorAuthMethod::TOTP],
                'methodToDelete' => MultiFactorAuthMethod::TOTP,
            ],
        ];
    }

    #[DataProvider('userMethodsProvider')]
    public function testReturnsUserMethods($mode, $allowedMethods, $userMethods, $expectedMethods, $forceMethod = MultiFactorAuthMethod::EMAIL)
    {
        $this->configureMFA(
            mode: $mode,
            allowedMethods: $allowedMethods,
            forceMethod: $forceMethod
        );

        $user = $this->makeUser($userMethods);

        $this->assertEqualsCanonicalizing($expectedMethods, $user->getUserMethods());
    }

    public static function userMethodsProvider(): array
    {
        return [
            'user_has_one_method' => [
                'mode' => MultiFactorAuthMode::OPTIONAL,
                'allowedMethods' => [MultiFactorAuthMethod::EMAIL->value],
                'userMethods' => [MultiFactorAuthMethod::EMAIL],
                'expectedMethods' => [MultiFactorAuthMethod::EMAIL],
            ],
            'user_has_multiple_methods' => [
                'mode' => MultiFactorAuthMode::OPTIONAL,
                'allowedMethods' => [MultiFactorAuthMethod::TOTP->value, MultiFactorAuthMethod::EMAIL->value],
                'userMethods' => [MultiFactorAuthMethod::TOTP, MultiFactorAuthMethod::EMAIL],
                'expectedMethods' => [MultiFactorAuthMethod::TOTP, MultiFactorAuthMethod::EMAIL],
            ],
            'user_has_one_allowed_and_one_unallowed_method' => [
                'mode' => MultiFactorAuthMode::OPTIONAL,
                'allowedMethods' => [MultiFactorAuthMethod::EMAIL->value],
                'userMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
                'expectedMethods' => [MultiFactorAuthMethod::EMAIL],
            ],
            'user_has_no_allowed_methods' => [
                'mode' => MultiFactorAuthMode::OPTIONAL,
                'allowedMethods' => [],
                'userMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
                'expectedMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
            ],
            'user_has_no_methods' => [
                'mode' => MultiFactorAuthMode::OPTIONAL,
                'allowedMethods' => [MultiFactorAuthMethod::EMAIL->value, MultiFactorAuthMethod::TOTP->value],
                'userMethods' => [],
                'expectedMethods' => [],
            ],
            'user_has_multiple_methods_in_force_mode' => [
                'mode' => MultiFactorAuthMode::FORCE,
                'allowedMethods' => [MultiFactorAuthMethod::TOTP->value, MultiFactorAuthMethod::EMAIL->value],
                'userMethods' => [MultiFactorAuthMethod::TOTP, MultiFactorAuthMethod::EMAIL],
                'expectedMethods' => [MultiFactorAuthMethod::EMAIL],
                'forceMethod' => MultiFactorAuthMethod::EMAIL,
            ],
        ];
    }

    #[DataProvider('userMethodsWithRemainingAllowedMethodsProvider')]
    public function testReturnsUserMethodsWithRemainingAllowedMethods($mode, $allowedMethods, $userMethods, $expectedMethods)
    {
        $this->configureMFA(mode: $mode, allowedMethods: $allowedMethods);

        $user = $this->makeUser($userMethods);

        $this->assertEqualsCanonicalizing($expectedMethods, $user->getUserMethodsWithRemainingAllowedMethods());
    }

    public static function userMethodsWithRemainingAllowedMethodsProvider()
    {
        return [
            'user_has_one_method' => [
                'mode' => MultiFactorAuthMode::OPTIONAL,
                'allowedMethods' => [MultiFactorAuthMethod::EMAIL->value],
                'userMethods' => [MultiFactorAuthMethod::EMAIL],
                'expectedMethods' => [MultiFactorAuthMethod::EMAIL],
            ],
            'user_has_multiple_methods' => [
                'mode' => MultiFactorAuthMode::OPTIONAL,
                'allowedMethods' => [MultiFactorAuthMethod::TOTP->value, MultiFactorAuthMethod::EMAIL->value],
                'userMethods' => [MultiFactorAuthMethod::TOTP, MultiFactorAuthMethod::EMAIL],
                'expectedMethods' => [MultiFactorAuthMethod::TOTP, MultiFactorAuthMethod::EMAIL],
            ],
            'user_has_one_allowed_and_one_unallowed_method' => [
                'mode' => MultiFactorAuthMode::OPTIONAL,
                'allowedMethods' => [MultiFactorAuthMethod::EMAIL->value],
                'userMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
                'expectedMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
            ],
            'user_has_no_allowed_methods' => [
                'mode' => MultiFactorAuthMode::OPTIONAL,
                'allowedMethods' => [],
                'userMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
                'expectedMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
            ],
            'user_has_no_methods' => [
                'mode' => MultiFactorAuthMode::OPTIONAL,
                'allowedMethods' => [MultiFactorAuthMethod::EMAIL->value, MultiFactorAuthMethod::TOTP->value],
                'userMethods' => [],
                'expectedMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
            ],
        ];
    }
}
