<x-multi-factor::layout>
    <x-slot name="title">@lang('multi-factor::auth.choose.title')</x-slot>

    <x-multi-factor::auth-card>
        <x-slot name="subtitle">
            @if($isVerified)
                <p>@lang('multi-factor::auth.choose.subtitle', ['action' => 'setup'])</p>
            @else
                <p>@lang('multi-factor::auth.choose.subtitle', ['action' => 'login'])</p>
            @endif
        </x-slot>

        @foreach($userMethods as $method)
            <a class="mfa-row mfa-list-item" href="{{ route('mfa.method', $method) }}">
                <div class="mfa-row">
                    <x-multi-factor::svg :icon="$method->value"/>
                    <p><strong>{{ ucfirst($method->value) }}</strong></p>
                </div>
            </a>
        @endforeach
    </x-multi-factor::auth-card>
</x-multi-factor::layout>
