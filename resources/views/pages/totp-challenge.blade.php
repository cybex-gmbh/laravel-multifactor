<x-multi-factor::layout>
    <x-slot name="title">@lang('multi-factor::auth.title')</x-slot>

    <x-multi-factor::auth-card>
        <x-slot name="subtitle">
            <p>@lang('multi-factor::auth.email_challenge.subtitle', ['authenticationMethod' => $authenticationMethod, 'email' => $user->email])</p>
        </x-slot>

        @if (isset($user->two_factor_confirmed_at))
            <x-multi-factor::form :action="route('two-factor.confirm')" id="mfa-confirm">
                <x-multi-factor::form.input field="code" label="Authentication Code" type="text" required autofocus autocomplete="one-time-code"/>
            </x-multi-factor::form>
        @else
            <x-multi-factor::form :action="route('two-factor.login.store')" id="mfa-confirm">
                <x-multi-factor::form.input field="code" label="Authentication Code" type="text" required autofocus autocomplete="one-time-code"/>
            </x-multi-factor::form>
        @endif

        <div class="mfa-row mfa-flex-end">
            <x-multi-factor::button type="submit" class="text-sm" form="mfa-login">
                {{ __('Log in') }}
            </x-multi-factor::button>
        </div>
    </x-multi-factor::auth-card>
</x-multi-factor::layout>
