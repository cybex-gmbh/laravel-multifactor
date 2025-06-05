<x-multi-factor::layout>
    <x-slot name="title">Choose 2FA Method</x-slot>

    <x-multi-factor::auth-card>
        <x-slot name="subtext">
            @if($isVerified)
                <p>Choose one of these methods to setup</p>
            @else
                <p>Choose one of these methods to log in</p>
            @endif
        </x-slot>

        @foreach($userMethods as $method)
            <a class="link flex flex-row section-underline" href="{{ route('mfa.method', $method) }}">
                <x-multi-factor::MFA-svg method="{{ $method }}"/>
                <p><strong>{{ ucfirst($method->value) }}</strong></p>
            </a>
        @endforeach
    </x-multi-factor::auth-card>
</x-multi-factor::layout>
