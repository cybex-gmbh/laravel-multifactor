<x-two-factor-layout>
    <x-slot name="title">Two Factor Auth Settings</x-slot>

    <x-multi-factor-auth-card>
        <x-slot name="header">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Two-Factor Authentication') }}
            </h1>
        </x-slot>

        <div class="form">
            <div class="">
                <div class="mt-6 text-gray-500">
                    {{ __('You can enable or disable two-factor authentication for your account.') }}
                </div>
            </div>

            <div>
                <div>
                    @if (auth()->user()->twoFactorAuthMethods()->exists())
                        <div class="flex-row align-center green">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="svg size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z"/>
                            </svg>

                            <h3 class="text-lg font-medium">
                                {{ __('Two-Factor Authentication is enabled') }}
                            </h3>
                        </div>
                    @else
                        <div class="flex-row align-center text-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="svg size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z"/>
                            </svg>
                            <h3 class="text-lg font-medium">
                                {{ __('Two-Factor Authentication is Disabled') }}
                            </h3>
                        </div>
                    @endif
                </div>
            </div>

            @foreach($methods as $method)
                <span class="flex-row align-center underline" href="{{ route('2fa.method', $method) }}">
                    <x-svg method="{{ $method }}"></x-svg>
                    <p><strong>{{ ucfirst($method->value) }}</strong></p>
                    @if (in_array($method, $user->getTwoFactorAuthMethods()))
                        <p>{{ __('Enabled') }}</p>
                        <form method="POST" action="{{ route('2fa.delete.method', $method) }}">
                            @csrf
                            @method('DELETE')
                            <x-form.button type="submit" class="button danger" confirm="Disable {{ $method->value }}?">{{ __('Disable') }}</x-form.button>
                        </form>
                    @else
                        <p>{{ __('Disabled') }}</p>
                        <form method="GET" action="{{ route('2fa.setup', $method) }}">
                            @csrf
                            <x-form.button type="submit" class="button">{{ __('Enable') }}</x-form.button>
                        </form>
                    @endif
                </span>
            @endforeach
        </div>

        {{--<div class="flex">
            @if (!auth()->user()->twoFactorAuthMethods()->exists())
                <form method="GET" action="{{ route('2fa.setup') }}">
                    @csrf

                    <button type="submit" class="button btn-large">
                        {{ __('Enable Two-Factor Authentication') }}
                    </button>
                </form>
            @else
                <form method="GET" action="{{ route('2fa.delete') }}">
                    @csrf

                    <x-form.button type="submit" class="button danger" confirm="Disable Two Factor Authentication?">{{ __('Disable Two-Factor Authentication') }}</x-form.button>
                </form>
            @endif
        </div>--}}
    </x-multi-factor-auth-card>
</x-two-factor-layout>