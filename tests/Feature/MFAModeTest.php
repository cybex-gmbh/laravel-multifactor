<?php

namespace Cybex\LaravelMultiFactor\Tests\Feature;

use App\Models\User;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use Cybex\LaravelMultiFactor\Notifications\MultiFactorCodeNotification;
use Cybex\LaravelMultiFactor\Tests\BaseTest;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use MFA;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Throws;
use PragmaRX\Google2FA\Google2FA;

class MFAModeTest extends BaseTest
{
    #[DataProvider('loginOptionalModeProvider')]
    public function testUserCanLoginInOptionalMode(array $allowedMethods, array $userMethods)
    {
        $this->configureMFA(allowedMethods: $allowedMethods);

        $user = $this->makeUser(...$userMethods);

        $this->login($user);

        $this->assertAuthenticated();
        $this->assertTrue(MFA::isVerified());
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
        $this->configureMFA(mode: $mode->value, allowedMethods: $allowedMethods);
        $user = $this->makeUser(...$userMethods);
        Notification::fake();

        $response = $this->login($user)->assertRedirect(route('mfa.show'));
        $finalResponse = $this->followRedirects($response);

        $this->assertCorrectMFARedirect($userMethods, $finalResponse, $methodToLogin);

        $this->loginWithMFAMethod($methodToLogin, $user);

        $this->assertAuthenticated();
        $this->assertTrue(MFA::isVerified());
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

    // required tests
    #[DataProvider('loginWithSetupInRequiredModeProvider')]
    public function testUserCanLoginWithSetupInRequiredMode(
        array $allowedMethods,
        array $userMethods,
        ?MultiFactorAuthMethod $methodToLogin = null,
        ?MultiFactorAuthMethod $methodToSetup = null
    ) {
        $this->configureMFA(mode: MultiFactorAuthMode::REQUIRED->value, allowedMethods: $allowedMethods);

        $user = $this->makeUser(...$userMethods);

        Notification::fake();

        $response = $this->login($user);

        $userMethodsNames = Arr::map($userMethods, fn($method) => $method->value);
        $hasAllowedMethods = array_intersect($userMethodsNames, $allowedMethods);

        $response->assertRedirect(route($userMethods ? 'mfa.show' : 'mfa.setup'));
        $finalResponse = $this->followRedirects($response);

        $this->assertCorrectMFARedirect($userMethods, $finalResponse, $methodToLogin);

        if ($userMethods) {
            $response = $this->loginWithMFAMethod($methodToLogin, $user);

            $this->assertGuest();
            $this->assertTrue(MFA::isVerified());
            $response->assertRedirect(route('mfa.setup'));

            $this->followRedirects($response);

            $currentRoute = Route::getCurrentRoute();

            $this->assertEquals(
                route($methodToSetup === MultiFactorAuthMethod::TOTP ? 'mfa.setup' : 'mfa.method', $methodToSetup),
                route($currentRoute->getName(), $currentRoute->parameters())
            );
        }

        if (!$hasAllowedMethods && $userMethods) {
            if ($methodToSetup === MultiFactorAuthMethod::TOTP) {
                $this->setupTotp();
            }

            $this->loginWithMFAMethod($methodToSetup, $user);

            $this->assertTrue($user->multiFactorAuthMethods->contains('type', $methodToSetup));
            $this->assertAuthenticated();
        }
    }

    public static function loginWithSetupInRequiredModeProvider(): array
    {
        return [
            'has only unallowed method' => [
                'allowedMethods' => ['email'],
                'userMethods' => [MultiFactorAuthMethod::TOTP],
                'methodToLogin' => MultiFactorAuthMethod::TOTP,
                'methodToSetup' => MultiFactorAuthMethod::EMAIL,
            ],
            'has no methods' => [
                'allowedMethods' => [],
                'userMethods' => [],
                'methodToLogin' => null,
                'methodToSetup' => MultiFactorAuthMethod::EMAIL,
            ],
        ];
    }

    #[DataProvider('loginWithSetupInForceModeProvider')]
    public function testUserCanLoginWithSetupInForceMode($allowedMethods, $userMethods, $methodToLogin, $forceMethod)
    {
        $this->configureMFA(mode: MultiFactorAuthMode::FORCE->value, allowedMethods: $allowedMethods, forceMethod: $forceMethod->value);

        $user = $this->makeUser(...$userMethods);

        Notification::fake();

        $response = $this->login($user);

        $userMethodsNames = Arr::map($userMethods, fn($method) => $method->value);
        $hasAllowedMethods = array_intersect($userMethodsNames, $allowedMethods);

        $response->assertRedirect(route(filled($userMethods) ? 'mfa.show' : 'mfa.setup'));

        $finalResponse = $this->followRedirects($response);
        $this->assertCorrectMFARedirect($userMethods, $finalResponse, $userMethods ? $methodToLogin : $forceMethod);

        $response = $this->loginWithMFAMethod($userMethods ? $methodToLogin : $forceMethod, $user);

        if (!$hasAllowedMethods && $userMethods) {

            $finalResponse = $this->followRedirects($response);
            $this->assertCorrectMFARedirect($userMethods, $finalResponse, $forceMethod);

            if ($forceMethod === MultiFactorAuthMethod::TOTP) {
                $this->setupTotp();
            }

            $this->loginWithMFAMethod($forceMethod, $user);
        }

        $this->assertAuthenticated();
        $this->assertTrue(MFA::isVerified());
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
            'has no methods' => [
                'allowedMethods' => ['email', 'totp'],
                'userMethods' => [],
                'methodToLogin' => null,
                'forceMethod' => MultiFactorAuthMethod::EMAIL,
            ],
        ];
    }

    /**
     * @return void
     */
    public function setupTotp(): void
    {
        $response = $this->post(route('two-factor.enable'), [
            '_token' => csrf_token(),
        ]);

        $this->followRedirects($response);

        $response = $this->post(route('password.confirm'), [
            'password' => 'password',
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect(route('mfa.setup', MultiFactorAuthMethod::TOTP));

        $this->get(route('mfa.method', MultiFactorAuthMethod::TOTP));

        $this->post(route('two-factor.confirm'), [
            'code' => 123456,
            '_token' => csrf_token(),
        ]);
    }

    /**
     * @param array $userMethods
     * @param Response|TestResponse $finalResponse
     * @param MultiFactorAuthMethod|null $methodToLogin
     * @return void
     */
    public function assertCorrectMFARedirect(array $userMethods, Response|TestResponse $finalResponse, ?MultiFactorAuthMethod $methodToLogin): void
    {
        if (count($userMethods) > 1) {
            if (!MultiFactorAuthMode::isForceMode()) {
                $this->assertEquals($userMethods, $finalResponse->viewData('userMethods'));
            }

            $this->get(route('mfa.method', $methodToLogin));
        } elseif ($userMethods) {
            $this->assertEquals('mfa.method', Route::currentRouteName());
        } else {
            if ($methodToLogin === MultiFactorAuthMethod::TOTP || !$methodToLogin) {
                $this->assertEquals('mfa.setup', Route::currentRouteName());
            } else {
                $this->assertEquals('mfa.method', Route::currentRouteName());
            }
        }
    }
}
