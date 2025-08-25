<div {{ $attributes->class('mfa-auth-card') }}>
    <div class="mfa-header">
        {!! sprintf('<h1>%s</h1>', $header ?? __('multi-factor::auth.title')) !!}
        {{ $subtitle ?? '' }}
    </div>

    {{ $slot }}
</div>
