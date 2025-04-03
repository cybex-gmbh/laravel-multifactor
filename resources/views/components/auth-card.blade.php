<div {{ $attributes->merge(['class' => 'auth-card']) }}>
    <div class="">
        <h1>Multi-factor Authentication</h1>
        {{ $header ?? '' }}
    </div>

    {{ $slot }}
</div>