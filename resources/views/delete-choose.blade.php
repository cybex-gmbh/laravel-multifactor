<x-multi-factor-layout>
    <x-slot name="title">Delete 2FA Method</x-slot>

    <x-multi-factor-auth-card>
        <x-slot name="subtext">
            <p>Choose one of these methods to delete</p>
        </x-slot>

        @foreach($userMethods as $method)
            <a class="btn flex flex-row" href="{{ route('mfa.method', $method) }}">
                <x-multi-factor-svg method="{{ $method }}"/>
                <p><strong>{{ ucfirst($method->value) }}</strong></p>
            </a>
        @endforeach
    </x-multi-factor-auth-card>
</x-multi-factor-layout>

