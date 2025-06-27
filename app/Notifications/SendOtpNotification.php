<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;

        $this->queue = 'send_otp';
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset Password OTP Code')
            ->line('Your OTP code is: ')
            ->line($this->otp)
            ->line('It will expire in 5 minutes.');
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
