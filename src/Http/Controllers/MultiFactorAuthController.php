<?php

namespace Cybex\LaravelMultiFactor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Cybex\LaravelMultiFactor\Contracts\MultiFactorChallengeViewResponseContract;
use Cybex\LaravelMultiFactor\Contracts\MultiFactorChooseViewResponseContract;
use Cybex\LaravelMultiFactor\Contracts\MultiFactorSettingsViewResponseContract;
use Cybex\LaravelMultiFactor\Contracts\MultiFactorSetupViewResponseContract;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use Cybex\LaravelMultiFactor\Http\Requests\MultiFactorLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse;
use Laravel\Fortify\Events\RecoveryCodeReplaced;
use Laravel\Fortify\Events\TwoFactorAuthenticationFailed;
use Laravel\Fortify\Events\ValidTwoFactorAuthenticationCodeProvided;
use MFA;

class MultiFactorAuthController extends Controller
{
    public function show(): mixed
    {
        $user = MFA::getUser();
        $availableMethods = $user->getUserMethods() ?: MFA::getAllowedMethods();

        if (MultiFactorAuthMode::isForceMode()) {
            $forceMethod = MFA::getForceMethod();

            if ($forceMethod->isUserMethod()) {
                return Redirect::route('mfa.method', ['method' => $forceMethod]);
            }
        }

        if (count($availableMethods) === 1) {
            return Redirect::route('mfa.method', ['method' => Arr::first($availableMethods)]);
        }

        return app(MultiFactorChooseViewResponseContract::class, $availableMethods);
    }

    public function handleMultiFactorAuthMethod(MultiFactorAuthMethod $method): MultiFactorChallengeViewResponseContract
    {
        return $method->getHandler()->challenge();
    }

    public function send(MultiFactorAuthMethod $method): RedirectResponse
    {
        return match ($method) {
            MultiFactorAuthMethod::EMAIL => $method->getHandler()->sendEmail(),
        };
    }

    public function setup(MultiFactorAuthMethod $method = null): RedirectResponse|MultiFactorChooseViewResponseContract|MultiFactorSetupViewResponseContract
    {
        $methods = $method?->isAllowed() ? [$method] : MFA::getAllowedMethods();

        if ($method) {
            if (!(MultiFactorAuthMode::isForceMode() && !$method->isForceMethod())) {
                return $method->getHandler()->showSetup();
            }
        }

        if (MultiFactorAuthMode::isForceMode()) {
            return Redirect::route('mfa.setup', ['method' => MFA::getForceMethod()]);
        }

        if (count($methods) === 1) {
            return Redirect::route('mfa.setup', ['method' => Arr::first($methods)]);
        }

        return app(MultiFactorChooseViewResponseContract::class, $methods);
    }

    public function deleteMultiFactorAuthMethod(MultiFactorAuthMethod $method, RedirectResponse $back = null): RedirectResponse
    {
        $method->getHandler()->delete();

        return $back ?? redirect()->back();
    }

    public function authenticateByEmailOnly(Request $request): RedirectResponse
    {
        $user = User::whereEmail($request->input('email'))->first();

        if (!$user) {
            return Redirect::back()->withErrors(['email' => __('auth.failed')]);
        }

        MFA::setLoginIdAndRemember($user, $request->boolean('remember'));

        if (!MultiFactorAuthMethod::EMAIL->isUserMethod() && !$user->getMultiFactorAuthMethods()) {
            MFA::setVerified();
            MFA::setSetupAfterLogin();
        }

        return redirect()->route('mfa.show');
    }

    public function multiFactorSettings(User $user)
    {
        if (Auth::user()->is($user) && !MultiFactorAuthMode::isForceMode()) {
            return app(MultiFactorSettingsViewResponseContract::class, [$user]);
        }

        abort(403);
    }

    public function store(MultiFactorLoginRequest $request, MultiFactorAuthMethod $method)
    {
        MFA::setLoginIdAndRemember(MFA::getUser(), $request->boolean('remember'));
        $user = $request->challengedUser();

        if ($code = $request->validRecoveryCode()) {
            $user->replaceRecoveryCode($code);

            event(new RecoveryCodeReplaced($user, $code));
        } elseif (!$request->hasValidMFACode($method)) {
            event(new TwoFactorAuthenticationFailed($user));

            return app(FailedTwoFactorLoginResponse::class)->toResponse($request);
        }

        event(new ValidTwoFactorAuthenticationCodeProvided($user));

        MFA::setLoginIdAndRemember($user, $request->boolean('remember'));

        if (!$method->isUserMethod()) {
            return $method->getHandler()->setup();
        }

        Mfa::setVerified();

        if (!$method->isAllowed() && !MultiFactorAuthMode::isOptionalMode() || MultiFactorAuthMode::isForceMode() && !$method->isForceMethod()) {
            MFA::setSetupAfterLogin();
            MFA::setSecret();

            return redirect()->route('mfa.setup');
        }

        MFA::login($request->remember());

        $request->session()->regenerate();

        return app(TwoFactorLoginResponse::class);
    }
}
