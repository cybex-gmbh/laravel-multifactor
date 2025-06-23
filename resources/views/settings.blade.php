@use(CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMode)
<x-multi-factor::layout>
    <x-slot name="title">@lang('multi-factor::auth.settings.title')</x-slot>

    <x-multi-factor::auth-card>
        <x-slot name="header">
            <h1>
                @lang('multi-factor::auth.settings.title')
            </h1>
        </x-slot>

        <div>
            <div>
                @lang('multi-factor::auth.settings.subtitle')
            </div>

            <div>
                <div>
                    @if ($user->multiFactorAuthMethods()->exists())
                        <div class="flex flex-row align-center text-active">
                            <x-multi-factor::svg icon="mfa-enabled"/>

                            <h3>
                                @lang('multi-factor::auth.status.enabled.message')
                            </h3>
                        </div>
                    @else
                        <div class="flex flex-row align-center text-danger">
                            <x-multi-factor::svg icon="mfa-disabled"/>

                            <h3>
                                @lang('multi-factor::auth.status.disabled.message')
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
                            <p class="text-center">@lang('multi-factor::auth.status.enabled.label')</p>

                            @if ($mfaMode === MultiFactorAuthMode::OPTIONAL || $userMethodsAmount > 1)
                                <x-multi-factor::form method="DELETE" :action="route('mfa.delete.method', $method)">
                                    <x-multi-factor::button type="submit" class="button-danger" confirm="Disable {{ $method->value }}?">@lang('multi-factor::button.disable')</x-multi-factor::button>
                                </x-multi-factor::form>
                            @endif
                        @else
                            <p class="text-center">@lang('multi-factor::auth.status.disabled.label')</p>

                            <x-multi-factor::form method="GET" :action="route('mfa.setup', $method)">
                                <x-multi-factor::button type="submit">@lang('multi-factor::button.enable')</x-multi-factor::button>
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
                    <x-multi-factor::button type="submit" class="button danger" confirm="Disable Multi Factor Authentication?">{{ __('Disable Multi-Factor Authentication') }}</x-multi-factor::button>
                </x-multi-factor::form>
            @endif
        </div>--}}
    </x-multi-factor::auth-card>
</x-multi-factor::layout>
