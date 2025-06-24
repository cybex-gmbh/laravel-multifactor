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
            <a class="link flex flex-row section-underline" href="{{ route('mfa.method', $method) }}">
                <x-multi-factor::svg :icon="$method->value"/>
                <p><strong>{{ ucfirst($method->value) }}</strong></p>
            </a>
        @endforeach
    </x-multi-factor::auth-card>
</x-multi-factor::layout>
