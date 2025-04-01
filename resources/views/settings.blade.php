<x-app-layout>
    <x-slot name="title">Two Factor Auth Settings</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Two-Factor Authentication') }}
        </h2>
    </x-slot>

    <div class="w-1/2 flex justify-between mb-4">
        <a href="{{ url()->previous() }}" title="Go Back">
            <x-form.button class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/>
                </svg>
            </x-form.button>
        </a>
    </div>

    <div class="form">
        <div class="">
            <div class="text-2xl text-black">
                {{ __('Manage Two-Factor Authentication') }}
            </div>

            <div class="mt-6 text-gray-500">
                {{ __('You can enable or disable two-factor authentication for your account.') }}
            </div>
        </div>

        <hr class="mt-4 mb-4">

        <div>
            <div>
                @if (session('status') == 'two-factor-authentication-enabled')
                    <div class="mb-4 font-medium text-sm text-black">
                        Please finish configuring two factor authentication below.
                    </div>
                @endif

                @if (auth()->user()->twoFactorAuthMethods()->exists())
                        <div class="flex gap-2 text-green-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z"/>
                            </svg>

                            <h3 class="text-lg font-medium">
                                {{ __('Two-Factor Authentication is enabled') }}
                            </h3>
                        </div>
                @else
                    <div class="flex gap-2 text-red-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
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
    </div>

    <div class="w-1/2 flex justify-between mb-4">
        @if (!auth()->user()->twoFactorAuthMethods()->exists())
            <form method="GET" action="{{ route('2fa.setup') }}">
                @csrf

                <button type="submit" class="btn-large">
                    {{ __('Enable Two-Factor Authentication') }}
                </button>
            </form>
        @else
            <form method="GET" action="{{ route('2fa.delete') }}">
                @csrf

                <x-form.button type="submit" class="btn-large danger" confirm="Disable Two Factor Authentication?">{{ __('Disable Two-Factor Authentication') }}</x-form.button>
            </form>
        @endif
    </div>
</x-app-layout>