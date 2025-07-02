<x-multi-factor::layout>
    <x-slot name="title">@lang('multi-factor::auth.title')</x-slot>

    <x-multi-factor::auth-card>
        <x-slot name="subtitle">
            <p>@lang('multi-factor::auth.email_challenge.subtitle', ['authenticationMethod' => $authenticationMethod, 'email' => $user->email])</p>
        </x-slot>

        <x-multi-factor::form :action="route('mfa.verify', $mfaMethod, $user)" id="mfa-login">
            <x-multi-factor::form.input field="code" label="Authentication Code" type="text" required autofocus autocomplete="one-time-code"/>
        </x-multi-factor::form>

        <div class="mfa-row mfa-flex-end">
            <p class="mfa-text-sm">@lang('multi-factor::auth.email_challenge.subtitle_resend', ['authenticationMethod' => $authenticationMethod])</p>

            <x-multi-factor::form :action="route('mfa.method.send', $mfaMethod)" id="resend-code-form">
                <x-multi-factor::button class="text-sm">@lang('multi-factor::button.resend_mfa', ['authenticationMethod' => $authenticationMethod])</x-multi-factor::button>
            </x-multi-factor::form>

            <x-multi-factor::button type="submit" class="text-sm" form="mfa-login">
                {{ __('Log in') }}
            </x-multi-factor::button>
        </div>
    </x-multi-factor::auth-card>
</x-multi-factor::layout>
