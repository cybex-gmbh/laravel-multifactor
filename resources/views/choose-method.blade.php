<x-guest-layout>
    <x-slot name="title">Choose 2FA Method</x-slot>

    <x-auth-card>
        <div>
            <p class="text-sm text-center mt-2">Select a verification method</p>
            <div class="mt-4 flex justify-center gap-2 flex-col">
                @foreach($userMethods as $method)
                    <a class="btn" href="{{ route('2fa.method', $method) }}">{{ $method->value }}</a>
                @endforeach
            </div>
        </div>
    </x-auth-card>
</x-guest-layout>
