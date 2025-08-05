<?php

namespace Cybex\LaravelMultiFactor\Tests\Feature;

use App\Models\User;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Cybex\LaravelMultiFactor\Models\MultiFactorAuthMethod as MultiFactorAuthMethodModel;
use Cybex\LaravelMultiFactor\Notifications\MultiFactorCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
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
    public function testLoginInOptionalMode(array $allowedMethods, array $userMethods)
    {
        $this->configureMFA(allowedMethods: $allowedMethods);

        $user = $this->makeUser(...$userMethods);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
            '_token' => csrf_token(),
        ]);

        $this->assertAuthenticated();
        $this->assertTrue(MFA::isVerified());
    }

    public static function loginOptionalModeProvider(): array
    {
        return [
            'user has unallowed method' => [
                'allowedMethods' => ['email'],
                'userMethods' => [MultiFactorAuthMethod::TOTP],
            ],
            'user has no methods' => [
                'allowedMethods' => [],
                'userMethods' => [],
            ],
        ];
    }

    #[DataProvider('allowedMethodsProvider')]
    public function testLoginInOptionalModeWithAllowedMethods(array $allowedMethods, array $userMethods)
    {
        $this->configureMFA(allowedMethods: $allowedMethods);

        $user = $this->makeUser(...$userMethods);

        Notification::fake();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect(route('mfa.show'));
        $finalResponse = $this->followRedirects($response);

        if (count($userMethods) > 1) {
            $this->assertContainsOnlyInstancesOf(MultiFactorAuthMethod::class, $finalResponse->viewData('userMethods'));
            $this->assertEquals($userMethods, $finalResponse->viewData('userMethods'));
        } else {
            $this->assertEquals('mfa.method', Route::currentRouteName());
        }

        $this->get(route('mfa.method', MultiFactorAuthMethod::EMAIL));

        $mfaCode = $this->assertMFAEmailSent($user);

        $this->assertGuest();

        $this->post(route('mfa.store', MultiFactorAuthMethod::EMAIL, $user), [
            'code' => $mfaCode,
            '_token' => csrf_token(),
        ]);

        $this->assertAuthenticated();
        $this->assertTrue(MFA::isVerified());
    }

    public static function allowedMethodsProvider(): array
    {
        return [
            'one allowed method' => [
                ['email'],
                [MultiFactorAuthMethod::EMAIL],
            ],
            'multiple allowed methods' => [
                ['email', 'totp'],
                [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
            ],
        ];
    }

    // required tests
    #[DataProvider('loginRequiredModeProvider')]
    public function testLoginInRequiredModeWithSetup(string $mode, array $allowedMethods, array $userMethods, ?MultiFactorAuthMethod $methodToLogin = null)
    {
        $this->configureMFA(mode: $mode, allowedMethods: $allowedMethods);

        $user = $this->makeUser(...$userMethods);

        Notification::fake();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
            '_token' => csrf_token(),
        ]);

        $userMethodsNames = Arr::map($userMethods, fn($method) => $method->value);
        $hasAllowedMethods = array_intersect($userMethodsNames, $allowedMethods);

        $response->assertRedirect(route($userMethods ? 'mfa.show' : 'mfa.setup'));
        $finalResponse = $this->followRedirects($response);

        if (count($userMethods) > 1) {
            $this->assertContainsOnlyInstancesOf(MultiFactorAuthMethod::class, $finalResponse->viewData('userMethods'));
            $this->assertEquals($userMethods, $finalResponse->viewData('userMethods'));

            $this->get(route('mfa.method', $methodToLogin));
        } elseif ($userMethods) {
            $this->assertEquals('mfa.method', Route::currentRouteName());
        } else {
            $this->assertEquals('mfa.setup', Route::currentRouteName());
        }

        if ($userMethods) {
            if ($methodToLogin === MultiFactorAuthMethod::TOTP) {
                $secret = decrypt($user->two_factor_secret);
                $google2fa = new Google2FA();
                $mfaCode = $google2fa->getCurrentOtp($secret);
            } else {
                $mfaCode = $this->assertMFAEmailSent($user);
            }

            $this->assertGuest();

            $response = $this->post(route('mfa.store', $methodToLogin, $user), [
                'code' => $mfaCode,
                '_token' => csrf_token(),
            ]);

            $this->assertGuest();
            $this->assertTrue(MFA::isVerified());

            $response->assertRedirect(route('mfa.setup'));
            $finalResponse = $this->followRedirects($response);

            $currentRoute = Route::getCurrentRoute();
            $this->assertEquals(route('mfa.method', MultiFactorAuthMethod::EMAIL), route($currentRoute->getName(), $currentRoute->parameters()));
            $this->assertEquals(MultiFactorAuthMethod::EMAIL, $finalResponse->viewData('mfaMethod'));
        }

        if (!$hasAllowedMethods && $userMethods) {
            if ($methodToLogin === MultiFactorAuthMethod::TOTP) {
                $this->post(route('mfa.store', MultiFactorAuthMethod::EMAIL), [
                    'code' => $this->assertMFAEmailSent($user),
                    '_token' => csrf_token(),
                ]);
            } else {
                $this->setupTotp();

                $secret = decrypt($user->two_factor_secret);
                $google2fa = new Google2FA();
                $mfaCode = $google2fa->getCurrentOtp($secret);

                $this->post(route('mfa.store', MultiFactorAuthMethod::TOTP), [
                    'code' => $mfaCode,
                    '_token' => csrf_token(),
                ]);
            }

            $this->assertTrue($user->multiFactorAuthMethods->contains('type', MultiFactorAuthMethod::EMAIL));
            $this->assertAuthenticated();
        } else {
            $this->assertEquals('mfa.setup', Route::currentRouteName());
        }
    }

    public static function loginRequiredModeProvider(): array
    {
        return [
            'user has unallowed method' => [
                'mode' => 'required',
                'allowedMethods' => ['email'],
                'userMethods' => [MultiFactorAuthMethod::TOTP],
                'methodToLogin' => MultiFactorAuthMethod::TOTP,
            ],
            'user has no methods' => [
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
}
