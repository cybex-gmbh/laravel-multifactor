<?php

namespace Cybex\LaravelMultiFactor\Tests\Feature;

use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use Cybex\LaravelMultiFactor\Tests\BaseTest;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;

class LimitMultiFactorAuthAccessMiddlewareTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function testUserCanAccessOnlyHisMethodsDuringMfaLoginInForceMode()
    {
        $this->configureMFA(mode: MultiFactorAuthMode::FORCE);

        $user = $this->makeUser([MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP]);

        $this->followRedirects($this->login($user));

        $this->assertCurrentRouteIs('mfa.method', ['method' => MultiFactorAuthMethod::EMAIL]);
        $this->assertInaccessibleMethodRedirects(MultiFactorAuthMethod::TOTP, MultiFactorAuthMethod::EMAIL);
    }

    #[DataProvider('provideForUserCanAccessOnlyHisMethodsDuringMfaLogin')]
    public function testUserCanAccessOnlyHisMethodsDuringMfaLogin($mode, $userMethods, $methodToLogin, $inaccessibleMethod)
    {
        $this->configureMFA(mode: $mode);

        $user = $this->makeUser($userMethods);

        $finalResponse = $this->followRedirects($this->login($user));

        $this->assertMFARedirectToExpectedRoute($userMethods, $finalResponse, $methodToLogin);
        $this->assertInaccessibleMethodRedirects($inaccessibleMethod, $methodToLogin);
    }

    public static function provideForUserCanAccessOnlyHisMethodsDuringMfaLogin(): array
    {
        return [
            'email optional' => [
                'mode' => MultiFactorAuthMode::OPTIONAL,
                'userMethods' => [MultiFactorAuthMethod::EMAIL],
                'methodToLogin' => MultiFactorAuthMethod::EMAIL,
                'inaccessibleMethod' => MultiFactorAuthMethod::TOTP,
            ],
            'totp optional' => [
                'mode' => MultiFactorAuthMode::OPTIONAL,
                'userMethods' => [MultiFactorAuthMethod::TOTP],
                'methodToLogin' => MultiFactorAuthMethod::TOTP,
                'inaccessibleMethod' => MultiFactorAuthMethod::EMAIL,
            ],
            'email required' => [
                'mode' => MultiFactorAuthMode::REQUIRED,
                'userMethods' => [MultiFactorAuthMethod::EMAIL],
                'methodToLogin' => MultiFactorAuthMethod::EMAIL,
                'inaccessibleMethod' => MultiFactorAuthMethod::TOTP,
            ],
            'totp required' => [
                'mode' => MultiFactorAuthMode::REQUIRED,
                'userMethods' => [MultiFactorAuthMethod::TOTP],
                'methodToLogin' => MultiFactorAuthMethod::TOTP,
                'inaccessibleMethod' => MultiFactorAuthMethod::EMAIL,
            ],
        ];
    }

    #[DataProvider('provideForUserCanNotAccessOtherMethodsDuringMfaSetup')]
    public function testUserCanNotAccessOtherMethodsDuringMfaSetup($mode, $allowedMethods, $userMethods, $methodToLogin, $methodToSetup, $inaccessibleMethod)
    {
        $this->configureMFA(mode: $mode, allowedMethods: $allowedMethods);

        $user = $this->makeUser($userMethods);

        $this->followRedirects($this->login($user));

        $finalResponse = $this->followRedirects($this->loginWithMFAMethod($methodToLogin, $user));

        $this->assertMFARedirectToExpectedRoute($userMethods, $finalResponse, $methodToSetup);

        $this->assertInaccessibleMethodRedirects($inaccessibleMethod, $methodToSetup);
    }

    public static function provideForUserCanNotAccessOtherMethodsDuringMfaSetup(): array
    {
        return [
            'email required' => [
                'mode' => MultiFactorAuthMode::REQUIRED,
                'allowedMethods' => ['email'],
                'userMethods' => [MultiFactorAuthMethod::TOTP],
                'methodToLogin' => MultiFactorAuthMethod::TOTP,
                'methodToSetup' => MultiFactorAuthMethod::EMAIL,
                'inaccessibleMethod' => MultiFactorAuthMethod::TOTP,
            ],
        ];
    }

    #[DataProvider('provideForUserCanAccessOtherMethodsDuringMfaLogin')]
    public function testUserCanAccessOtherMethodsDuringMfaLogin($userMethods, $methodToLogin, $otherMethod)
    {
        $this->configureMFA();

        $user = $this->makeUser($userMethods);

        $this->followRedirects($this->login($user));

        $this->assertMethodIsAccessible($methodToLogin);
        $this->assertMethodIsAccessible($otherMethod);
    }

    public static function provideForUserCanAccessOtherMethodsDuringMfaLogin(): array
    {
        return [
            'email optional' => [
                'userMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
                'methodToLogin' => MultiFactorAuthMethod::EMAIL,
                'otherMethod' => MultiFactorAuthMethod::TOTP,
            ],
        ];
    }

    protected function assertMethodIsAccessible($method): void
    {
        $this->get(route('mfa.method', ['method' => $method]))->assertStatus(200);
    }

    protected function assertInaccessibleMethodRedirects($inaccessibleMethod, $methodToLogin): void
    {
        $response = $this->get(route('mfa.method', ['method' => $inaccessibleMethod]));

        $this->followRedirects($response);

        $this->assertCurrentRouteIs('mfa.method', ['method' => $methodToLogin]);
    }
}
