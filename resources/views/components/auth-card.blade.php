<div {{ $attributes->class('mfa-auth-card') }}>
    <div class="mfa-header">
        {!! $header ?? sprintf('<h1>%s</h1>', __('multi-factor::auth.title')) !!}
        {{ $subtitle ?? '' }}
    </div>

    {{ $slot }}
</div>
