<?php

namespace Cybex\LaravelMultiFactor\Tests;

use App\Models\User;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use Cybex\LaravelMultiFactor\Models\MultiFactorAuthMethod as MultiFactorAuthMethodModel;
use Cybex\LaravelMultiFactor\Notifications\MultiFactorCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Laravel\Fortify\TwoFactorAuthenticationProvider;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use MFA;


abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected const TOTP_SECRET_FIELD = 'two_factor_secret';
    protected const TOTP_CONFIRMED_AT_FIELD = 'two_factor_confirmed_at';

    public function configureMFA(MultiFactorAuthMode $mode = MultiFactorAuthMode::OPTIONAL, array $allowedMethods = ['email', 'totp'], MultiFactorAuthMethod $forceMethod = MultiFactorAuthMethod::EMAIL, bool $emailOnlyMode = false): void
    {
        config()->set([
            'multi-factor.mode' => $mode->value,
            'multi-factor.allowedMethods' => $allowedMethods,
            'multi-factor.forceMethod' => $forceMethod->value,
            'multi-factor.features.email-login.enabled' => $emailOnlyMode,
        ]);
    }

    public function makeUser(array $methods): User
    {
        $attributes = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ];

        $user = User::make($attributes);

        $user->saveQuietly();

        if (filled($methods)) {
            $this->addMFAMethodsToUser($user, $methods);
        }

        return $user;
    }

    public function addMFAMethodsToUser(User $user, array $methods): void
    {
        if (in_array(MultiFactorAuthMethod::TOTP, $methods)) {
            $provider = app(TwoFactorAuthenticationProvider::class);
            $secret = $provider->generateSecretKey();

            $totpAttributes[self::TOTP_SECRET_FIELD] = encrypt($secret);
            $totpAttributes[self::TOTP_CONFIRMED_AT_FIELD] = now();
        }

        if (isset($totpAttributes)) {
            $user->{self::TOTP_SECRET_FIELD} = $totpAttributes[self::TOTP_SECRET_FIELD];
            $user->{self::TOTP_CONFIRMED_AT_FIELD} = $totpAttributes[self::TOTP_CONFIRMED_AT_FIELD];
            $user->saveQuietly();
        }

        foreach ($methods as $method) {
            $user->multiFactorAuthMethods()->attach(MultiFactorAuthMethodModel::firstOrCreate([
                'type' => $method,
            ]));
        }
    }

    public function login(User $user): TestResponse
    {
        return $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
            '_token' => csrf_token(),
        ]);
    }

    public function loginWithEmailOnlyAndRedirect(User $user): TestResponse
    {
        $response = $this->post(route('mfa.email.login'), [
            'email' => $user->email,
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect(route('mfa.show'));

        return $this->followRedirects($response);
    }

    public function loginAndRedirect(User $user): TestResponse
    {
        $response = $this->login($user);

        $response->assertRedirect(route(filled($user->getMultiFactorAuthMethods()) ? 'mfa.show' : 'mfa.setup'));

        return $this->followRedirects($response);
    }

    public function loginWithMFAMethod(MultiFactorAuthMethod $method, User $user): TestResponse
    {
        if ($method === MultiFactorAuthMethod::TOTP) {
            $user->refresh();
            $secret = decrypt($user->{self::TOTP_SECRET_FIELD});
            $google2fa = new Google2FA();
            $mfaCode = $google2fa->getCurrentOtp($secret);

            if (!$user->hasTotpConfirmed()) {
                return $this->post(route('two-factor.confirm'), [
                    'code' => $mfaCode,
                    '_token' => csrf_token(),
                ]);
            }
        } else {
            $this->get(route('mfa.method', MultiFactorAuthMethod::EMAIL));
            $mfaCode = $this->assertMFAEmailSent($user);
        }

        return $this->post(route('mfa.store', $method), [
            'code' => $mfaCode,
            '_token' => csrf_token(),
        ]);
    }

    public function loginWithMFAMethodAndRedirect(MultiFactorAuthMethod $method, User $user): TestResponse
    {
        $response = $this->loginWithMFAMethod($method, $user);

        return $this->followRedirects($response);
    }

    protected function assertMFAEmailSent(User $user): ?string
    {
        $mfaCode = null;

        Notification::assertSentTo($user, MultiFactorCodeNotification::class, function ($notification) use (&$mfaCode, $user) {
            $body = $notification->toMail($user)->render();

            if (preg_match('/MFA code: (\d{6})/', $body, $matches)) {
                $mfaCode = $matches[1];
            }

            return true;
        });

        return $mfaCode;
    }

    protected function assertUserDoesNotHaveMethod($user, $method): void
    {
        $this->assertFalse($user->multiFactorAuthMethods->contains('type', $method));
    }

    protected function assertUserHasMethod($user, $method): void
    {
        $this->assertTrue($user->multiFactorAuthMethods->contains('type', $method));
    }

    protected function assertCurrentRouteIs($routeName, $params = []): void
    {
        $currentRoute = Route::getCurrentRoute();
        $this->assertEquals(route($routeName, $params), route($currentRoute->getName(), $currentRoute->parameters()));
    }

    protected function assertMFARedirectToExpectedRoute($userMethods, TestResponse $response, $methodToLogin): void
    {
        if (!$methodToLogin) {
            $this->assertCurrentRouteIs('mfa.setup');
            return;
        }

        $nextRouteName = $methodToLogin->doesNeedUserSetup() && !$methodToLogin->isUserMethod() ? 'mfa.setup' : 'mfa.method';

        if (count($userMethods) > 1 && !MultiFactorAuthMode::isForceMode()) {
            $this->assertCurrentRouteIs('mfa.show');
            $this->assertEquals($userMethods, $response->viewData('userMethods'));

            $this->get(route($nextRouteName, $methodToLogin));
        }

        $this->assertCurrentRouteIs($nextRouteName, $methodToLogin);
    }

    protected function assertMultiFactorAuthenticated(): void
    {
        $this->assertAuthenticated();
        $this->assertTrue(MFA::isVerified());
    }

    protected function assertGuestRedirectedToMFASetup(Response|TestResponse $response): void
    {
        $this->assertGuest();
        $this->assertTrue(MFA::isVerified());
        $response->assertRedirect(route('mfa.setup'));
    }
}
