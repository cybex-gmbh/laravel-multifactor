<div {{ $attributes->merge(['class' => 'auth-card']) }}>
    <div class="header">
        @isset($header)
            {{ $header }}
        @else
            <h1>Multi-factor Authentication</h1>
        @endisset
        {{ $subtext ?? '' }}
    </div>

    {{ $slot }}
</div>
