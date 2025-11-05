<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class UserCreated extends Notification
{
    use Queueable;

    protected $plainPassword;

    public function __construct(string $plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Your New Account Credentials')
                    ->greeting("Welcome, {$notifiable->name}!")
                    ->line('Your new account has been successfully created. Here are your temporary login details:')
                    ->line('**Email:** ' . $notifiable->email)
                    ->line('**Temporary Password:** ' . $this->plainPassword)
                    ->line('Please log in and change your password immediately.')
                    ->action('Login to OPEX System', url('/login'));
    }
}