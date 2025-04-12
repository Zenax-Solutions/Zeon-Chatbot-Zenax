<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OpenRouterDownNotification extends Notification
{
    use Queueable;

    protected string $errorMessage;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('OpenRouter API is DOWN')
            ->line('The OpenRouter API health check failed.')
            ->line('Error: ' . $this->errorMessage)
            ->line('Please investigate the issue as soon as possible.');
    }
}
