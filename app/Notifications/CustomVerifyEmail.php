<?php

namespace App\Notifications;

use App\Helpers\Helpers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CustomVerifyEmail extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
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
    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Email Address')
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('This verification link will expire in 5 minutes.')
            ->line('If you did not create an account, no further action is required.');
    }

    // /**
    //  * Get the array representation of the notification.
    //  *
    //  * @return array<string, mixed>
    //  */
    // public function toArray(object $notifiable): array
    // {
    //     return [
    //         //
    //     ];
    // }

    protected function verificationUrl($notifiable)
    {
        // Create a verification token with user data
        $tokenData = [
            'user_id' => $notifiable->getKey(),
            'email' => $notifiable->getEmailForVerification(),
            'expires_at' => Carbon::now()->addMinutes(5)->timestamp,
            'random' => Str::random(10) // Add randomness for extra security
        ];

        // Encrypt the entire token data
        $encryptedToken = Helpers::encrypt(json_encode($tokenData));

        // Create the verification URL with encrypted token
        return url('/api/email/verify/' . urlencode($encryptedToken));
    }
}
