<?php

namespace CybexGmbh\LaravelTwoFactor\Services;

use App\Models\User;
use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorChallengeViewResponseContract;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthMethod;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthSession;
use CybexGmbh\LaravelTwoFactor\Notifications\TwoFactorCodeNotification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;

class TwoFactorAuthService
{
    public function handleTwoFactorAuthMethod(User $user, TwoFactorAuthMethod $method)
    {
        return match ($method) {
            TwoFactorAuthMethod::EMAIL => $this->handleEmailAuthentication($user, $method),
        };
    }

    protected function handleEmailAuthentication(User $user, TwoFactorAuthMethod $method)
    {
        $sessionKey = TwoFactorAuthSession::EMAIL_SENT;

        if (!session()->has($sessionKey->value)) {
            $this->sendEmail($user);
            $sessionKey->put();
        }

        return app(MultiFactorChallengeViewResponseContract::class, [$user, $method]);
    }

    public function send(User|Authenticatable|null $user, TwoFactorAuthMethod $method): RedirectResponse
    {
        return match ($method) {
            TwoFactorAuthMethod::EMAIL => $this->sendEmail($user),
        };
    }

    protected function sendEmail(User $user): RedirectResponse
    {
        $code = random_int(100000, 999999);
        $userKey = $user->getKey();

        TwoFactorAuthSession::CODE->put($code);

        $user->notify(new TwoFactorCodeNotification(TwoFactorAuthMethod::EMAIL, $code, $userKey));

        return redirect()->back();
    }

    public function handleTwoFactorAuthSetup(User|Authenticatable|null $user, TwoFactorAuthMethod $method): RedirectResponse
    {
        return match ($method) {
            TwoFactorAuthMethod::EMAIL => $this->setupEmail($user),
            default => abort(404)
        };
    }

    protected function setupEmail(User $user): RedirectResponse
    {
        $user->twoFactorAuthMethods()->firstOrCreate([
            'type' => TwoFactorAuthMethod::EMAIL,
        ]);

        session()->remove(TwoFactorAuthSession::SETUP_IN_PROCESS->value);
        return redirect()->route('2fa.show');
    }
}
