@use(Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMode)
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
                        <div class="mfa-row mfa-text-active">
                            <x-multi-factor::svg icon="mfa-enabled"/>

                            <h3>
                                @lang('multi-factor::auth.status.enabled.message')
                            </h3>
                        </div>
                    @else
                        <div class="mfa-row mfa-text-danger">
                            <x-multi-factor::svg icon="mfa-disabled"/>

                            <h3>
                                @lang('multi-factor::auth.status.disabled.message')
                            </h3>
                        </div>
                    @endif
                </div>
            </div>

            @foreach($methods as $method)
                <div class="mfa-row mfa-list-item">
                    <div class="mfa-row">
                        <x-multi-factor::svg :icon="$method->value"/>
                        <p><strong>{{ ucfirst($method->value) }}</strong></p>
                    </div>

                    <div class="mfa-row">
                        @if ($method->isUserMethod())
                            <p>@lang('multi-factor::auth.status.enabled.label')</p>

                            @if ($mfaMode === MultiFactorAuthMode::OPTIONAL || $userMethodsAmount > 1)
                                <x-multi-factor::form method="DELETE" :action="route('mfa.delete.method', $method)">
                                    <x-multi-factor::button type="submit" class="mfa-button-danger" confirm="Disable {{ $method->value }}?">@lang('multi-factor::button.disable')</x-multi-factor::button>
                                </x-multi-factor::form>
                            @else
                                <x-multi-factor::button class="mfa-button-disabled">@lang('multi-factor::button.disable')</x-multi-factor::button>
                            @endif
                        @else
                            <p>@lang('multi-factor::auth.status.disabled.label')</p>

                            <x-multi-factor::form method="GET" :action="route('mfa.setup', $method)">
                                <x-multi-factor::button type="submit">@lang('multi-factor::button.enable')</x-multi-factor::button>
                            </x-multi-factor::form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </x-multi-factor::auth-card>
</x-multi-factor::layout>
