<x-multi-factor::layout>
    <x-slot name="title">@lang('multi-factor::auth.email_login.title')</x-slot>

    <x-multi-factor::auth-card>
        <x-multi-factor::form :action="route('mfa.email.login')">
            <x-multi-factor::form.input field="email" label="E-Mail Address" type="email" required autofocus/>
            <x-multi-factor::form.input field="password" label="Password" type="password" required autofocus/>

            <div>
                <label for="remember_me">
                    <input id="remember_me" type="checkbox" name="remember">
                    <span>@lang('multi-factor::auth.remember_me')</span>
                </label>
            </div>

            <div class="mfa-row mfa-flex-end">
                <x-multi-factor::button type="submit">
                    @lang('multi-factor::button.login')
                </x-multi-factor::button>
            </div>
        </x-multi-factor::form>
    </x-multi-factor::auth-card>
</x-multi-factor::layout>
