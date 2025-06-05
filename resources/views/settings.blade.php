@use(CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMode)
<x-multi-factor::layout>
    <x-slot name="title">Multi Factor Auth Settings</x-slot>

    <x-multi-factor::auth-card>
        <x-slot name="header">
            <h1>
                {{ __('Manage Multi-Factor Authentication') }}
            </h1>
        </x-slot>

        <div>
            <div>
                {{ __('You can enable or disable multi-factor authentication for your account.') }}
            </div>

            <div>
                <div>
                    @if ($user->multiFactorAuthMethods()->exists())
                        <div class="flex flex-row align-center text-active">
                            <x-multi-factor::svg icon="mfa-enabled"/>

                            <h3>
                                {{ __('Multi-Factor Authentication is enabled') }}
                            </h3>
                        </div>
                    @else
                        <div class="flex flex-row align-center text-danger">
                            <x-multi-factor::svg icon="mfa-disabled"/>

                            <h3>
                                {{ __('Multi-Factor Authentication is Disabled') }}
                            </h3>
                        </div>
                    @endif
                </div>
            </div>

            @foreach($methods as $method)
                <div class="flex justify-between section-underline">
                    <div class="flex flex-row">
                        <x-multi-factor::MFA-svg method="{{ $method }}"/>
                        <p class="text-center"><strong>{{ ucfirst($method->value) }}</strong></p>
                    </div>

                    <div class="flex flex-row">
                        @if ($method->isUserMethod())
                            <p class="text-center">{{ __('Enabled') }}</p>

                            @if ($mfaMode === MultiFactorAuthMode::OPTIONAL || $userMethodsAmount > 1)
                                <x-multi-factor::form method="DELETE" :action="route('mfa.delete.method', $method)">
                                    <x-form.button type="submit" class="button button-danger" confirm="Disable {{ $method->value }}?">{{ __('Disable') }}</x-form.button>
                                </x-multi-factor::form>
                            @endif
                        @else
                            <p class="text-center">{{ __('Disabled') }}</p>

                            <x-multi-factor::form method="GET" :action="route('mfa.setup', $method)">
                                <x-form.button type="submit" class="button">{{ __('Enable') }}</x-form.button>
                            </x-multi-factor::form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{--<div class="flex">
            @if (!auth()->user()->multiFactorAuthMethods()->exists())
                <x-multi-factor::form method="GET" :action="route('mfa.setup')">
                    <button type="submit" class="button btn-large">
                        {{ __('Enable Multi-Factor Authentication') }}
                    </button>
                </x-multi-factor::form>
            @else
                <x-multi-factor::form method="GET" :action="route('mfa.delete')">
                    <x-form.button type="submit" class="button danger" confirm="Disable Two Factor Authentication?">{{ __('Disable Multi-Factor Authentication') }}</x-form.button>
                </x-multi-factor::form>
            @endif
        </div>--}}
    </x-multi-factor::auth-card>
</x-multi-factor::layout>
