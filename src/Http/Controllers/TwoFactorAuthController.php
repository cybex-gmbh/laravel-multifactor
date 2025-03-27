<?php

namespace CybexGmbh\LaravelTwoFactor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthMethod;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthMode;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthSession;
use CybexGmbh\LaravelTwoFactor\Services\TwoFactorAuthService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

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

    public function show(): View|RedirectResponse
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

        return view('laravel-two-factor::choose-method', compact('userMethods'));
    }

    public function handleTwoFactorAuthMethod(TwoFactorAuthMethod $method): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
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

        return view('laravel-two-factor::setup', compact('methods'));
    }

    public function handleDeletion(): \Illuminate\Contracts\Foundation\Application|Factory|View|Application|RedirectResponse
    {
        $methods = Auth::user()->getTwoFactorAuthMethods();
        $back = Redirect::back();

        if (count($methods) === 1) {
            return $this->deleteTwoFactorAuthMethod($methods[0], $back);
        }

        return view('laravel-two-factor::delete-choose', compact('methods', 'back'));
    }

    public function deleteTwoFactorAuthMethod(TwoFactorAuthMethod $method, RedirectResponse $back): RedirectResponse
    {
        Auth::user()->twoFactorAuthMethods()->where('type', $method)->delete();
        session()->remove(TwoFactorAuthSession::VERIFIED->value);

        return $back;
    }

    public function verifyTwoFactorAuthCode(TwoFactorAuthMethod $method, User $user = null, int $code = null): Application|Redirector|RedirectResponse
    {
        $code ??= request()->integer('code') ?? abort(403);

        if ($code !== TwoFactorAuthSession::CODE->get()) {
            abort(403);
        }

        TwoFactorAuthSession::clear();
        TwoFactorAuthSession::VERIFIED->put();

        return redirect('/projects');
    }
}
