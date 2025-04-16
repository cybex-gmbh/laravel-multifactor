<?php

namespace CybexGmbh\LaravelMultiFactor\Notifications;

use Carbon\Carbon;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthSession;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class MultiFactorCodeNotification extends Notification
{
    use Queueable;

    protected string $url;

    public function __construct(?string $url)
    {
        $this->url = $url;
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('New Login Request')
            ->when($this->url, fn($message) => $message
                ->line('Click the link below to login:')
                ->action('Login', $this->url)
                ->line('OR'))
            ->line(sprintf('You can use the following MFA code: %s', MultiFactorAuthSession::getCode()))
            ->line(sprintf('The %s will expire in 10 minutes.', $this->url ? 'link and code' : 'code'));
    }
}
