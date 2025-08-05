<?php

namespace Cybex\LaravelMultiFactor\Tests\Feature;

use App\Models\User;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use Cybex\LaravelMultiFactor\Models\MultiFactorAuthMethod as MultiFactorAuthMethodModel;
use Cybex\LaravelMultiFactor\Notifications\MultiFactorCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Laravel\Fortify\TwoFactorAuthenticationProvider;
use MFA;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Throws;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class MFAModeTest extends TestCase
{
    use RefreshDatabase;

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
    #[DataProvider('loginWithSetupProvider')]
    public function testUserCanLoginWithSetup(string $mode, array $allowedMethods, array $userMethods, ?MultiFactorAuthMethod $methodToLogin = null)
    {
        $this->configureMFA(mode: $mode, allowedMethods: $allowedMethods);

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
            $currentRoute = route($currentRoute->getName(), $currentRoute->parameters());

            if ($methodToLogin === MultiFactorAuthMethod::TOTP) {
                $this->assertEquals(route('mfa.method', MultiFactorAuthMethod::EMAIL), $currentRoute);
            } else {
                $this->assertEquals(route('mfa.setup', MultiFactorAuthMethod::TOTP), $currentRoute);
            }
        }

        if (!$hasAllowedMethods && $userMethods) {
            if ($methodToLogin === MultiFactorAuthMethod::TOTP) {
                $this->loginWithMFAMethod(MultiFactorAuthMethod::EMAIL, $user);
            } else {
                $this->setupTotp();

                $this->loginWithMFAMethod(MultiFactorAuthMethod::TOTP, $user);
            }

            $this->assertTrue($user->multiFactorAuthMethods->contains('type', MultiFactorAuthMethod::EMAIL));
            $this->assertAuthenticated();
        }
    }

    public static function loginWithSetupProvider(): array
    {
        return [
            'has only unallowed method' => [
                'mode' => 'required',
                'allowedMethods' => ['email'],
                'userMethods' => [MultiFactorAuthMethod::TOTP],
                'methodToLogin' => MultiFactorAuthMethod::TOTP,
            ],
            'has no methods' => [
                'mode' => 'required',
                'allowedMethods' => [],
                'userMethods' => [],
                'methodToLogin' => MultiFactorAuthMethod::EMAIL,
            ],
        ];
    }

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

    /**
     * @param User $user
     * @return string|null
     * @throws \Exception
     */
    public function assertMFAEmailSent(User $user): ?string
    {
        $mfaCode = null;

        Notification::assertSentTo($user, MultiFactorCodeNotification::class, function ($notification) use (&$mfaCode, $user) {
            $mailMessage = $notification->toMail($user);
            $body = $mailMessage->render();

            if (preg_match('/You can use the following MFA code: (\d{6})/', $body, $matches)) {
                $mfaCode = $matches[1];
            }

            return true;
        });

        return $mfaCode;
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
     * @param User $user
     * @return TestResponse
     */
    public function login(User $user): TestResponse
    {
        return $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
            '_token' => csrf_token(),
        ]);
    }

    public function loginWithMFAMethod(MultiFactorAuthMethod $method, User $user): TestResponse
    {
        if ($method === MultiFactorAuthMethod::TOTP) {
            $secret = decrypt($user->two_factor_secret);
            $google2fa = new Google2FA();
            $mfaCode = $google2fa->getCurrentOtp($secret);
        } else {
            $this->get(route('mfa.method', MultiFactorAuthMethod::EMAIL));
            $mfaCode = $this->assertMFAEmailSent(MFA::getUser());
        }

        return $this->post(route('mfa.store', $method), [
            'code' => $mfaCode,
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
            $this->assertEquals('mfa.setup', Route::currentRouteName());
        }
    }
}
