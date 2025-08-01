<?php

namespace Cybex\LaravelMultiFactor\Tests\Feature;

use App\Models\User;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Cybex\LaravelMultiFactor\Models\MultiFactorAuthMethod as MultiFactorAuthMethodModel;
use Cybex\LaravelMultiFactor\Notifications\MultiFactorCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\TwoFactorAuthenticationProvider;
use MFA;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;

class MFAModeTest extends TestCase
{
    use RefreshDatabase;

    public function testLoginInOptionalModeWithOnlyUnallowedMethods()
    {
        $this->configureMFA(allowedMethods: ['email']);

        $user = $this->makeUser(MultiFactorAuthMethod::TOTP);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
            '_token' => csrf_token(),
        ]);

        $this->assertAuthenticated();
        $this->assertTrue(MFA::isVerified());
    }

    public function testLoginInOptionalModeWithNoMethods()
    {
        $this->configureMFA();

        $user = $this->makeUser();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
            '_token' => csrf_token(),
        ]);

        $this->assertAuthenticated();
        $this->assertTrue(MFA::isVerified());
    }

    public function testLoginInOptionalModeWithOneAllowedMethod()
    {
        $this->configureMFA();

        $user = $this->makeUser(MultiFactorAuthMethod::EMAIL);

        Notification::fake();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect(route('mfa.show'));
        $this->followRedirects($response);
        $this->assertEquals('mfa.method', Route::currentRouteName());

        $mfaCode = null;

        Notification::assertSentTo($user, MultiFactorCodeNotification::class, function ($notification, $channels) use (&$mfaCode, $user) {
            $mailMessage = $notification->toMail($user);
            $body = $mailMessage->render();

            if (preg_match('/You can use the following MFA code: (\d{6})/', $body, $matches)) {
                $mfaCode = $matches[1];
            }

            return true;
        });

        $this->assertGuest();

        $response = $this->post(route('mfa.store', MultiFactorAuthMethod::EMAIL, $user), [
            'code' => $mfaCode,
            '_token' => csrf_token(),
        ]);

        $this->assertAuthenticated();
        $this->assertTrue(MFA::isVerified());
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
            $totpAttributes['two_factor_confirmed_at'] = true;
        }

        if (isset($totpAttributes)) {
            $user->fill($totpAttributes);
            $user->saveQuietly();
        }

        foreach ($methods as $method) {
            $user->multiFactorAuthMethods()->attach(MultiFactorAuthMethodModel::firstOrCreate([
                'type' => $method,
            ]));
        }
    }
}
