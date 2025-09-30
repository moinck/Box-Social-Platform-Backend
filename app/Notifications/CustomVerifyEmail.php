<?php

namespace App\Notifications;

use App\Helpers\Helpers;
use App\Mail\RegisterVerificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CustomVerifyEmail extends Notification
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): RegisterVerificationMail
    {
        // $verificationUrl = "http://178.128.45.173:9163/email/verification-success/";

        return new RegisterVerificationMail($this->token);
    }


    protected function verificationUrl($token)
    {
        return url('/api/email/verify/' . urlencode($token));
    }
}
