<x-guest-layout>
    <x-slot name="title">Choose 2FA Method</x-slot>

    <x-auth-card>
        <div>
            <p class="text-sm text-center mt-2">Select a verification method</p>
            <div class="mt-4 flex justify-center gap-2 flex-col">
                @foreach($methods as $method)
                    <form class="btn" method="POST" action="{{ route('2fa.delete.method', $method, $back) }}">{{ $method->value }}</form>
                @endforeach
            </div>
        </div>
    </x-auth-card>
</x-guest-layout>
