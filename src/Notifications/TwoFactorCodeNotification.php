<?php

namespace CybexGmbh\LaravelTwoFactor\Notifications;

use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthMethod;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class TwoFactorCodeNotification extends Notification
{
    use Queueable;

    protected TwoFactorAuthMethod $method;
    protected int $code;
    protected int $userKey;

    public function __construct(TwoFactorAuthMethod $method, int $code, int $userKey)
    {
        $this->method = $method;
        $this->code = $code;
        $this->userKey = $userKey;
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        $url = URL::temporarySignedRoute(
            '2fa.login',
            now()->addMinutes(10),
            [
                'method' => $this->method,
                'user' => $this->userKey,
                'code' => $this->code
            ]
        );

        return (new MailMessage)
            ->subject('New Login Request')
            ->line('Click the link below to login:')
            ->action('Login', $url)
            ->line('You can alternatively use the following 2FA codee: ' . $this->code)
            ->line('The url and code will expire in 10 minutes.');
    }
}
