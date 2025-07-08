<x-multi-factor::layout>
    <x-slot name="title">@lang('multi-factor::auth.title')</x-slot>

    <x-multi-factor::auth-card>
        {{--        <x-slot name="subtitle">--}}
        {{--            <p>@lang('multi-factor::auth.email_challenge.subtitle', ['authenticationMethod' => $authenticationMethod, 'email' => $user->email])</p>--}}
        {{--        </x-slot>--}}

        @empty(request()->user()->two_factor_confirmed_at)
            <x-multi-factor::form :action="route('two-factor.enable')" id="fortify-totp">
                <x-multi-factor::button type="submit" class="text-sm" form="fortify-totp">
                    {{ __('Enable Totp') }}
                </x-multi-factor::button>
            </x-multi-factor::form>
        @endempty

        @if (session('status') == 'two-factor-authentication-enabled')
            <div class="mb-4 font-medium text-sm">
                Please finish configuring two factor authentication below.
            </div>
        @endif

        @if(isset($user->two_factor_secret) && empty($user->two_factor_confirmed_at))
            <div style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                <p>Scan the QR code below using an authenticator app</p>
                {!! request()->user()->twoFactorQrCodeSvg() !!}
            </div>
        @endif

        @if(empty(request()->user()->two_factor_confirmed_at) && isset(request()->user()->two_factor_secret))
            <x-multi-factor::form :action="route('two-factor.confirm')" id="mfa-confirm">
                <x-multi-factor::form.input field="code" label="Authentication Code" type="text" required autofocus autocomplete="one-time-code"/>
            </x-multi-factor::form>
        @endif

        @if (session('status') == 'two-factor-authentication-confirmed')
            <div class="mb-4 font-medium text-sm">
                Two factor authentication confirmed and enabled successfully.
            </div>
        @endif


        @if (empty(request()->user()->two_factor_confirmed_at))
            <div class="mfa-row mfa-flex-end">
                <x-multi-factor::button type="submit" class="text-sm" form="mfa-confirm">
                    {{ __('Submit Code') }}
                </x-multi-factor::button>
            </div>
        @endif
    </x-multi-factor::auth-card>
</x-multi-factor::layout>
