<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WhatsAppTokenRefreshFailed extends Notification
{
    use Queueable;

    public $integrationId;
    public $error;

    public function __construct($integrationId, $error)
    {
        $this->integrationId = $integrationId;
        $this->error = $error;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('WhatsApp Token Refresh Failed')
            ->line("Failed to refresh WhatsApp token for integration ID: {$this->integrationId}")
            ->line("Error: {$this->error}")
            ->line('Please check the integration and refresh the token manually if needed.');
    }
}
