<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class VerifyEmailThera extends BaseVerifyEmail
{
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        return (new MailMessage)
            ->subject(Lang::get('Verify Email Address'))
            ->view('vendor.mail.html.verify', [
                'actionUrl' => $verificationUrl,
                'user' => $notifiable,
            ]);
    }
}
