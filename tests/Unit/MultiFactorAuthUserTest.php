<?php

namespace Cybex\LaravelMultiFactor\Tests\Unit;

use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use Cybex\LaravelMultiFactor\Http\Controllers\MultiFactorAuthController;
use Cybex\LaravelMultiFactor\Tests\TestCase;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\DataProvider;

class MultiFactorAuthUserTest extends TestCase
{
    #[DataProvider('deleteMfaMethodProvider')]
    public function testDeletesUsersMfaMethod($allowedMethods, $userMethods, $methodToDelete)
    {
        $this->configureMFA(allowedMethods: $allowedMethods);

        $user = $this->makeUser($userMethods);

        $this->actingAs($user);

        app(MultiFactorAuthController::class)->deleteMultiFactorAuthMethod($methodToDelete);

        if ($methodToDelete === MultiFactorAuthMethod::TOTP) {
            $this->assertNull($user->{self::TOTP_SECRET_FIELD});
            $this->assertNull($user->{self::TOTP_CONFIRMED_AT_FIELD});
        }

        $this->assertUserDoesNotHaveMethod($user, $methodToDelete);

        foreach (Arr::where($userMethods, fn($method) => $method !== $methodToDelete) as $method) {
            $this->assertUserHasMethod($user, $method);
        }
    }

    public static function deleteMfaMethodProvider(): array
    {
        return [
            'user has email and totp and deletes email' => [
                'allowedMethods' => [MultiFactorAuthMethod::EMAIL->value],
                'userMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
                'methodToDelete' => MultiFactorAuthMethod::EMAIL,
            ],
            'user has totp and deletes totp' => [
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
            'user has one method' => [
                'mode' => MultiFactorAuthMode::OPTIONAL,
                'allowedMethods' => [MultiFactorAuthMethod::EMAIL->value],
                'userMethods' => [MultiFactorAuthMethod::EMAIL],
                'expectedMethods' => [MultiFactorAuthMethod::EMAIL],
            ],
            'user has multiple methods' => [
                'mode' => MultiFactorAuthMode::OPTIONAL,
                'allowedMethods' => [MultiFactorAuthMethod::TOTP->value, MultiFactorAuthMethod::EMAIL->value],
                'userMethods' => [MultiFactorAuthMethod::TOTP, MultiFactorAuthMethod::EMAIL],
                'expectedMethods' => [MultiFactorAuthMethod::TOTP, MultiFactorAuthMethod::EMAIL],
            ],
            'user has one allowed and one unallowed method' => [
                'mode' => MultiFactorAuthMode::OPTIONAL,
                'allowedMethods' => [MultiFactorAuthMethod::EMAIL->value],
                'userMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
                'expectedMethods' => [MultiFactorAuthMethod::EMAIL],
            ],
            'user has no allowed methods' => [
                'mode' => MultiFactorAuthMode::OPTIONAL,
                'allowedMethods' => [],
                'userMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
                'expectedMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
            ],
            'user has no methods' => [
                'mode' => MultiFactorAuthMode::OPTIONAL,
                'allowedMethods' => [MultiFactorAuthMethod::EMAIL->value, MultiFactorAuthMethod::TOTP->value],
                'userMethods' => [],
                'expectedMethods' => [],
            ],
            'user has multiple methods in force mode' => [
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

    public static function userMethodsWithRemainingAllowedMethodsProvider(): array
    {
        return [
            'user has one method' => [
                'mode' => MultiFactorAuthMode::OPTIONAL,
                'allowedMethods' => [MultiFactorAuthMethod::EMAIL->value],
                'userMethods' => [MultiFactorAuthMethod::EMAIL],
                'expectedMethods' => [MultiFactorAuthMethod::EMAIL],
            ],
            'user has multiple methods' => [
                'mode' => MultiFactorAuthMode::OPTIONAL,
                'allowedMethods' => [MultiFactorAuthMethod::TOTP->value, MultiFactorAuthMethod::EMAIL->value],
                'userMethods' => [MultiFactorAuthMethod::TOTP, MultiFactorAuthMethod::EMAIL],
                'expectedMethods' => [MultiFactorAuthMethod::TOTP, MultiFactorAuthMethod::EMAIL],
            ],
            'user has one allowed and one unallowed method' => [
                'mode' => MultiFactorAuthMode::OPTIONAL,
                'allowedMethods' => [MultiFactorAuthMethod::EMAIL->value],
                'userMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
                'expectedMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
            ],
            'user has no allowed methods' => [
                'mode' => MultiFactorAuthMode::OPTIONAL,
                'allowedMethods' => [],
                'userMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
                'expectedMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
            ],
            'user has no methods' => [
                'mode' => MultiFactorAuthMode::OPTIONAL,
                'allowedMethods' => [MultiFactorAuthMethod::EMAIL->value, MultiFactorAuthMethod::TOTP->value],
                'userMethods' => [],
                'expectedMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
            ],
        ];
    }
}
