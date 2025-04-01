<?php

namespace CybexGmbh\LaravelTwoFactor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorChooseViewResponseContract;
use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorDeleteViewResponseContract;
use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorSettingsViewResponseContract;
use CybexGmbh\LaravelTwoFactor\Contracts\MultiFactorSetupViewResponseContract;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthMethod;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthMode;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthSession;
use CybexGmbh\LaravelTwoFactor\Services\TwoFactorAuthService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

use Symfony\Component\HttpKernel\Exception\HttpException;

use function Illuminate\Support\Facades\abort;
use function Illuminate\Support\Facades\redirect;
use function Illuminate\Support\Facades\request;
use function Illuminate\Support\Facades\session;
use function Illuminate\Support\Facades\view;

class TwoFactorAuthController extends Controller
{
    protected TwoFactorAuthService $twoFactorAuthService;

    public function __construct(TwoFactorAuthService $twoFactorAuthService)
    {
        $this->twoFactorAuthService = $twoFactorAuthService;
    }

    public function show(): mixed
    {
        $user = Auth::user();
        $userMethods = $user->getTwoFactorAuthMethods();
        $configuredMode = TwoFactorAuthMode::fromConfig();

        switch ($configuredMode) {
            case TwoFactorAuthMode::FORCE:
                $forceMethod = TwoFactorAuthMethod::getForceMethod();

                if ($forceMethod->isUserMethod()) {
                    return Redirect::route('2fa.method', ['method' => $forceMethod]);
                }
                break;

            case TwoFactorAuthMode::OPTIONAL:
                if (!count(array_intersect($user->getTwoFactorAuthMethodsNames(), TwoFactorAuthMethod::getAllowedMethodsNames())) && !TwoFactorAuthSession::SETUP_IN_PROCESS->get()) {
                    TwoFactorAuthSession::VERIFIED->put();
                }
                break;
        }

        if (count($userMethods) === 1) {
            return Redirect::route('2fa.method', ['method' => $userMethods[0]]);
        }

        return app(MultiFactorChooseViewResponseContract::class, [$userMethods]);
    }

    public function handleTwoFactorAuthMethod(TwoFactorAuthMethod $method)
    {
        return $this->twoFactorAuthService->handleTwoFactorAuthMethod(Auth::user(), $method);
    }

    public function send(TwoFactorAuthMethod $method): RedirectResponse
    {
        return $this->twoFactorAuthService->send(Auth::user(), $method);
    }

    public function handleTwoFactorAuthSetup(TwoFactorAuthMethod $method): RedirectResponse
    {
        return $this->twoFactorAuthService->handleTwoFactorAuthSetup(Auth::user(), $method);
    }

    public function setup(): View|Application|Factory|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        TwoFactorAuthSession::SETUP_IN_PROCESS->put();

        $mode = TwoFactorAuthMode::fromConfig();
        $forceMethod = TwoFactorAuthMethod::getForceMethod();
        $methods = TwoFactorAuthMethod::getAllowedMethods();

        if ($mode === TwoFactorAuthMode::FORCE) {
            return Redirect::route('2fa.setup.method', ['method' => $forceMethod]);
        }

        if (count($methods) === 1) {
            return Redirect::route('2fa.setup.method', ['method' => $methods[0]]);
        }

        return app(MultiFactorSetupViewResponseContract::class, $methods);
    }

    public function handleDeletion(): \Illuminate\Contracts\Foundation\Application|Factory|View|Application|RedirectResponse
    {
        $methods = Auth::user()->getTwoFactorAuthMethods();
        $back = Redirect::back();

        if (count($methods) === 1) {
            return $this->deleteTwoFactorAuthMethod($methods[0], $back);
        }

        return app(MultiFactorDeleteViewResponseContract::class, compact('methods', 'back'));
    }

    public function deleteTwoFactorAuthMethod(TwoFactorAuthMethod $method, RedirectResponse $back): RedirectResponse
    {
        Auth::user()->twoFactorAuthMethods()->where('type', $method)->delete();
        TwoFactorAuthSession::VERIFIED->remove();

        return $back;
    }

    public function verifyTwoFactorAuthCode(Request $request, TwoFactorAuthMethod $method, User $user = null, int $code = null): Application|Redirector|RedirectResponse
    {
        $code ??= $request->integer('code') ?? throw new HttpException(403);

        if ($code !== TwoFactorAuthSession::CODE->get()) {
            // abort(403);
            throw new HttpException(403);
        }

        TwoFactorAuthSession::clear();
        TwoFactorAuthSession::VERIFIED->put();

        return Redirect::intended();
    }

    public function emailLogin(Request $request): RedirectResponse
    {
        $user = User::whereEmail($request->input('email'))->first();

        if (!$user) {
            return Redirect::back()->withErrors(['email' => __('auth.failed')]);
        }

        Auth::login($user);

        return Redirect::intended();
    }

    public function twoFactorSettings(User $user)
    {
        if (Auth::user()->is($user) && TwoFactorAuthMode::fromConfig() === TwoFactorAuthMode::OPTIONAL) {
            return app(MultiFactorSettingsViewResponseContract::class, [$user]);
        }

        // abort(403);
        throw new HttpException(403);
    }
}
