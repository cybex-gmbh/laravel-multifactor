<?php

namespace Cybex\LaravelMultiFactor\Tests\Feature;

use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use Cybex\LaravelMultiFactor\Tests\BaseTest;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Throws;

class MultiFactorAuthModeTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    #[DataProvider('loginOptionalModeProvider')]
    public function testUserCanLoginInOptionalMode(array $allowedMethods, array $userMethods)
    {
        $this->configureMFA(allowedMethods: $allowedMethods);

        $this->login($this->makeUser($userMethods));

        $this->assertMultiFactorAuthenticated();
    }

    public static function loginOptionalModeProvider(): array
    {
        return [
            'has only unallowed method' => [
                'allowedMethods' => ['email'],
                'userMethods' => [MultiFactorAuthMethod::TOTP],
            ],
            'has no methods configured' => [
                'allowedMethods' => [],
                'userMethods' => [],
            ],
        ];
    }

    #[DataProvider('loginWithAllowedMethodsProvider')]
    public function testUserCanLoginWithAllowedMethods(array $allowedMethods, array $userMethods, MultiFactorAuthMethod $methodToLogin, MultiFactorAuthMode $mode)
    {
        $this->configureMFA(mode: $mode, allowedMethods: $allowedMethods);

        $user = $this->makeUser($userMethods);
        $response = $this->loginAndRedirect($user);

        $this->assertMFARedirectToExpectedRoute($userMethods, $response, $methodToLogin);

        $this->loginWithMFAMethod($methodToLogin, $user);

        $this->assertMultiFactorAuthenticated();
    }

    public static function loginWithAllowedMethodsProvider(): array
    {
        return [
            'optional mode with one method' => [
                'allowedMethods' => ['email'],
                'userMethods' => [MultiFactorAuthMethod::EMAIL],
                'methodToLogin' => MultiFactorAuthMethod::EMAIL,
                'mode' => MultiFactorAuthMode::OPTIONAL,
            ],
            'optional mode with multiple methods' => [
                'allowedMethods' => ['email', 'totp'],
                'userMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
                'methodToLogin' => MultiFactorAuthMethod::TOTP,
                'mode' => MultiFactorAuthMode::OPTIONAL,
            ],
            'required mode with one method' => [
                'allowedMethods' => ['email'],
                'userMethods' => [MultiFactorAuthMethod::EMAIL],
                'methodToLogin' => MultiFactorAuthMethod::EMAIL,
                'mode' => MultiFactorAuthMode::REQUIRED,
            ],
            'required mode with multiple methods' => [
                'allowedMethods' => ['email', 'totp'],
                'userMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
                'methodToLogin' => MultiFactorAuthMethod::TOTP,
                'mode' => MultiFactorAuthMode::REQUIRED,
            ],
            'force mode with one method' => [
                'allowedMethods' => ['email'],
                'userMethods' => [MultiFactorAuthMethod::EMAIL],
                'methodToLogin' => MultiFactorAuthMethod::EMAIL,
                'mode' => MultiFactorAuthMode::FORCE,
            ],
            'force mode with multiple methods' => [
                'allowedMethods' => ['email', 'totp'],
                'userMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
                'methodToLogin' => MultiFactorAuthMethod::EMAIL,
                'mode' => MultiFactorAuthMode::FORCE,
            ],
        ];
    }

    #[DataProvider('loginWithSetupInRequiredModeProvider')]
    public function testUserCanLoginWithSetupInRequiredMode(
        array $allowedMethods,
        array $userMethods,
        ?MultiFactorAuthMethod $methodToLogin = null,
        ?MultiFactorAuthMethod $methodToSetup = null
    ) {
        $this->configureMFA(mode: MultiFactorAuthMode::REQUIRED, allowedMethods: $allowedMethods);

        $user = $this->makeUser($userMethods);
        $response = $this->loginAndRedirect($user);

        $this->assertMFARedirectToExpectedRoute($userMethods, $response, $methodToLogin);

        if ($userMethods) {
            $response = $this->loginWithMFAMethod($methodToLogin, $user);

            $this->assertGuestRedirectedToMFASetup($response);

            $finalResponse = $this->followRedirects($response);

            $this->assertMFARedirectToExpectedRoute($userMethods, $finalResponse, $methodToSetup);
        }

        if (!$user->refresh()->hasAllowedMultiFactorAuthMethods()) {
            if ($methodToSetup->doesNeedUserSetup()) {
                $this->setupMethod($methodToSetup);
            }

            $this->loginWithMFAMethod($methodToSetup, $user);

            $this->assertUserHasMethod($user->refresh(), $methodToSetup);
        }

        $this->assertMultiFactorAuthenticated();
    }

    public static function loginWithSetupInRequiredModeProvider(): array
    {
        return [
            'has only unallowed method setup email' => [
                'allowedMethods' => ['email'],
                'userMethods' => [MultiFactorAuthMethod::TOTP],
                'methodToLogin' => MultiFactorAuthMethod::TOTP,
                'methodToSetup' => MultiFactorAuthMethod::EMAIL,
            ],
            'has only unallowed method setup totp' => [
                'allowedMethods' => ['totp'],
                'userMethods' => [MultiFactorAuthMethod::EMAIL],
                'methodToLogin' => MultiFactorAuthMethod::EMAIL,
                'methodToSetup' => MultiFactorAuthMethod::TOTP,
            ],
            'has no methods setup email' => [
                'allowedMethods' => ['email', 'totp'],
                'userMethods' => [],
                'methodToLogin' => null,
                'methodToSetup' => MultiFactorAuthMethod::EMAIL,
            ],
        ];
    }

    #[DataProvider('loginWithSetupInForceModeProvider')]
    public function testUserCanLoginWithSetupInForceMode($allowedMethods, $userMethods, $methodToLogin, $forceMethod)
    {
        $this->configureMFA(mode: MultiFactorAuthMode::FORCE, allowedMethods: $allowedMethods, forceMethod: $forceMethod);

        $user = $this->makeUser($userMethods);
        $response = $this->loginAndRedirect($user);

        $this->assertMFARedirectToExpectedRoute($userMethods, $response, $methodToLogin);

        $response = $this->loginWithMFAMethod($methodToLogin, $user);

        if (!$user->refresh()->hasAllowedMultiFactorAuthMethods()) {
            $finalResponse = $this->followRedirects($response);
            $this->assertMFARedirectToExpectedRoute($userMethods, $finalResponse, $forceMethod);

            if ($forceMethod->doesNeedUserSetup()) {
                $this->setupMethod($forceMethod);
            }

            $this->loginWithMFAMethod($forceMethod, $user);
        }

        $this->assertMultiFactorAuthenticated();
    }

    public static function loginWithSetupInForceModeProvider(): array
    {
        return [
            'has only unallowed method' => [
                'allowedMethods' => ['email'],
                'userMethods' => [MultiFactorAuthMethod::TOTP],
                'methodToLogin' => MultiFactorAuthMethod::TOTP,
                'forceMethod' => MultiFactorAuthMethod::EMAIL,
            ],
            'has no methods, force method is email' => [
                'allowedMethods' => ['email', 'totp'],
                'userMethods' => [],
                'methodToLogin' => MultiFactorAuthMethod::EMAIL,
                'forceMethod' => MultiFactorAuthMethod::EMAIL,
            ],
            'has no methods, force method is totp' => [
                'allowedMethods' => ['email', 'totp'],
                'userMethods' => ['email'],
                'methodToLogin' => MultiFactorAuthMethod::EMAIL,
                'forceMethod' => MultiFactorAuthMethod::TOTP,
            ],
        ];
    }

    protected function setupMethod(MultiFactorAuthMethod $method): void
    {
        switch ($method) {
            case MultiFactorAuthMethod::TOTP:
                $this->setupTotp();
                break;
        }
    }

    protected function setupTotp(): void
    {
        $response = $this->post(route('two-factor.enable'), [
            '_token' => csrf_token(),
        ]);

        $this->followRedirects($response);

        if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')) {
            $response = $this->post(route('password.confirm.store'), [
                'password' => 'password',
                '_token' => csrf_token(),
            ]);

            $response->assertRedirect(route('mfa.setup', MultiFactorAuthMethod::TOTP));

            $this->followRedirects($response);

            $response = $this->post(route('two-factor.enable'), [
                '_token' => csrf_token(),
            ]);

            $this->followRedirects($response);
        }

        $this->get(route('mfa.method', MultiFactorAuthMethod::TOTP));

        $this->post(route('two-factor.confirm'), [
            'code' => 123456,
            '_token' => csrf_token(),
        ]);
    }
}
