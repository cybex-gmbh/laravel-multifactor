<?php

namespace Cybex\LaravelMultiFactor\Tests;

use App\Models\User;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Cybex\LaravelMultiFactor\Models\MultiFactorAuthMethod as MultiFactorAuthMethodModel;
use Cybex\LaravelMultiFactor\Notifications\MultiFactorCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Laravel\Fortify\TwoFactorAuthenticationProvider;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;
use MFA;


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

    protected function assertUserDoesNotHaveMethod($user, $method): void
    {
        $this->assertFalse($user->multiFactorAuthMethods->contains('type', $method));
    }

    protected function assertUserHasMethod($user, $method): void
    {
        $this->assertTrue($user->multiFactorAuthMethods->contains('type', $method));
    }
}