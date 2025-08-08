<?php

namespace Cybex\LaravelMultiFactor\Tests\Feature;

use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Cybex\LaravelMultiFactor\Tests\BaseTest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;

class LimitMFATest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function testUserCanAccessOnlyHisMethodsDuringMfaLoginInForceMode()
    {
        $this->configureMFA(mode: 'force', allowedMethods: ['email', 'totp'], forceMethod: 'email');

        $user = $this->makeUser(MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP);

        $response = $this->login($user);
        $this->followRedirects($response);

        $currentRoute = Route::getCurrentRoute();

        $this->assertCurrentRouteIs('mfa.method', ['method' => MultiFactorAuthMethod::EMAIL]);

        $this->get(route('mfa.method', ['method' => MultiFactorAuthMethod::TOTP]))
            ->assertRedirect(route('mfa.method', ['method' => MultiFactorAuthMethod::EMAIL]));
    }

    #[DataProvider('provideForUserCanAccessOnlyHisMethodsDuringMfaLogin')]
    public function testUserCanAccessOnlyHisMethodsDuringMfaLogin($mode, $userMethods, $methodToLogin, $inaccessibleMethod)
    {
        $this->configureMFA(mode: $mode);

        $user = $this->makeUser(...$userMethods);

        $response = $this->login($user);
        $finalResponse = $this->followRedirects($response);

        $this->assertMFARedirect($userMethods, $finalResponse, $methodToLogin);

        $response = $this->get(route('mfa.method', ['method' => $inaccessibleMethod]));
        $this->followRedirects($response);
        $this->assertCurrentRouteIs('mfa.method', ['method' => $methodToLogin]);
    }

    public static function provideForUserCanAccessOnlyHisMethodsDuringMfaLogin(): array
    {
        return [
            'email optional' => [
                'mode' => 'optional',
                'userMethods' => [MultiFactorAuthMethod::EMAIL],
                'methodToLogin' => MultiFactorAuthMethod::EMAIL,
                'inaccessibleMethod' => MultiFactorAuthMethod::TOTP,
            ],
            'totp optional' => [
                'mode' => 'optional',
                'userMethods' => [MultiFactorAuthMethod::TOTP],
                'methodToLogin' => MultiFactorAuthMethod::TOTP,
                'inaccessibleMethod' => MultiFactorAuthMethod::EMAIL,
            ],
            'email required' => [
                'mode' => 'required',
                'userMethods' => [MultiFactorAuthMethod::EMAIL],
                'methodToLogin' => MultiFactorAuthMethod::EMAIL,
                'inaccessibleMethod' => MultiFactorAuthMethod::TOTP,
            ],
            'totp required' => [
                'mode' => 'required',
                'userMethods' => [MultiFactorAuthMethod::TOTP],
                'methodToLogin' => MultiFactorAuthMethod::TOTP,
                'inaccessibleMethod' => MultiFactorAuthMethod::EMAIL,
            ],
        ];
    }

    #[DataProvider('provideForUserCanNotAccessOtherMethodsDuringMfaSetup')]
    public function testUserCanNotAccessOtherMethodsDuringMfaSetup($mode, $allowedMethods, $userMethods, $methodToLogin, $inaccessibleMethod)
    {
        $this->configureMFA(mode: $mode, allowedMethods: $allowedMethods);

        $user = $this->makeUser(...$userMethods);

        $response = $this->login($user);
        $this->followRedirects($response);

        $response = $this->loginWithMFAMethod($methodToLogin, $user);
        $finalResponse = $this->followRedirects($response);

        $this->assertMFARedirect($userMethods, $finalResponse, $methodToLogin, true);

        $this->get(route('mfa.method', $inaccessibleMethod))->assertRedirect(route('mfa.method', $methodToLogin));
    }

    public static function provideForUserCanNotAccessOtherMethodsDuringMfaSetup(): array
    {
        return [
            'email optional' => [
                'mode' => 'required',
                'allowedMethods' => ['email'],
                'userMethods' => [MultiFactorAuthMethod::TOTP],
                'methodToLogin' => MultiFactorAuthMethod::TOTP,
                'inaccessibleMethod' => MultiFactorAuthMethod::TOTP,
            ],
        ];
    }

    #[DataProvider('provideForUserCanAccessOtherMethodsDuringMfaLogin')]
    public function testUserCanAccessOtherMethodsDuringMfaLogin($userMethods, $methodToLogin, $otherMethod)
    {
        $this->configureMFA(mode: 'optional', allowedMethods: ['email', 'totp'], forceMethod: 'email');

        $user = $this->makeUser(...[MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP]);

        $response = $this->login($user);
        $this->followRedirects($response);

        $this->get((route('mfa.method', ['method' => $methodToLogin])))
            ->assertStatus(200);

        $this->get(route('mfa.method', ['method' => $otherMethod]))
            ->assertStatus(200);
    }

    public static function provideForUserCanAccessOtherMethodsDuringMfaLogin(): array
    {
        return [
            'email optional' => [
                'userMethods' => [MultiFactorAuthMethod::EMAIL],
                'methodToLogin' => MultiFactorAuthMethod::EMAIL,
                'otherMethod' => MultiFactorAuthMethod::TOTP,
            ],
        ];
    }

    private function assertCurrentRouteIs($routeName, $params = [])
    {
        $currentRoute = Route::getCurrentRoute();
        $this->assertEquals(route($routeName, $params), route($currentRoute->getName(), $currentRoute->parameters()));
    }

    public function assertMFARedirect($userMethods, Response|TestResponse $finalResponse, $methodToLogin, bool $isInSetup = false): void
    {
        $currentRoute = Route::getCurrentRoute();

        if (count($userMethods) > 1) {
            $this->assertEquals(route('mfa.show'), $currentRoute->getName());
            $this->assertEquals($userMethods, $finalResponse->viewData('userMethods'));
            $this->get(route('mfa.method', $methodToLogin));
        } else {
            if ($isInSetup) {
                $this->assertEquals(
                    $methodToLogin === MultiFactorAuthMethod::TOTP ? 'mfa.method' : 'mfa.setup',
                    $currentRoute->getName()
                );
            } else {
                $this->assertEquals(
                    route('mfa.method', $methodToLogin),
                    route($currentRoute->getName(), $currentRoute->parameters())
                );
            }
        }
    }
}
