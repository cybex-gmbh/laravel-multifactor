@php use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMode; @endphp
<x-multi-factor-layout>
    <x-slot name="title">Two Factor Auth Settings</x-slot>

    <x-multi-factor-auth-card>
        <x-slot name="header">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Multi-Factor Authentication') }}
            </h1>
        </x-slot>

        <div class="form">
            <div class="">
                <div class="mt-6 text-gray-500">
                    {{ __('You can enable or disable multi-factor authentication for your account.') }}
                </div>
            </div>

            <div>
                <div>
                    @if ($user->multiFactorAuthMethods()->exists())
                        <div class="flex-row align-center green">
                            <x-svg icon="mfa-enabled"/>

                            <h3 class="text-lg font-medium">
                                {{ __('Multi-Factor Authentication is enabled') }}
                            </h3>
                        </div>
                    @else
                        <div class="flex-row align-center text-danger">
                            <x-svg icon="mfa-disabled"/>

                            <h3 class="text-lg font-medium">
                                {{ __('Multi-Factor Authentication is Disabled') }}
                            </h3>
                        </div>
                    @endif
                </div>
            </div>

            @foreach($methods as $method)
                <div class="grid align-center underline" href="{{ route('mfa.method', $method) }}">
                    <x-mfa-svg method="{{ $method }}"></x-mfa-svg>
                    <p class="text-center"><strong>{{ ucfirst($method->value) }}</strong></p>

                    @if ($method->isUserMethod())
                        <p class="text-center">{{ __('Enabled') }}</p>

                        @if ($mfaMode === MultiFactorAuthMode::OPTIONAL || $userMethodsAmount > 1)
                            <form method="POST" action="{{ route('mfa.delete.method', $method) }}">
                                @csrf
                                @method('DELETE')
                                <x-form.button type="submit" class="button button-danger" confirm="Disable {{ $method->value }}?">{{ __('Disable') }}</x-form.button>
                            </form>
                        @endif
                    @else
                        <p class="text-center">{{ __('Disabled') }}</p>

                        <form method="GET" action="{{ route('mfa.setup', $method) }}">
                            @csrf
                            <x-form.button type="submit" class="button">{{ __('Enable') }}</x-form.button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        {{--<div class="flex">
            @if (!auth()->user()->multiFactorAuthMethods()->exists())
                <form method="GET" action="{{ route('mfa.setup') }}">
                    @csrf

                    <button type="submit" class="button btn-large">
                        {{ __('Enable Multi-Factor Authentication') }}
                    </button>
                </form>
            @else
                <form method="GET" action="{{ route('mfa.delete') }}">
                    @csrf

                    <x-form.button type="submit" class="button danger" confirm="Disable Two Factor Authentication?">{{ __('Disable Multi-Factor Authentication') }}</x-form.button>
                </form>
            @endif
        </div>--}}
    </x-multi-factor-auth-card>
</x-multi-factor-layout>
