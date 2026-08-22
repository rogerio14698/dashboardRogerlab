<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServerAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $type,
        public readonly string $severity,
        public readonly array $context = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(sprintf('[%s] Rogerlab: %s', strtoupper($this->severity), $this->type))
            ->line($this->context['message'] ?? 'Se ha detectado una alerta en el servidor.')
            ->line(json_encode($this->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
