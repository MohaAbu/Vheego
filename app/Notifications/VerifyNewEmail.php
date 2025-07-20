<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerifyNewEmail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $newEmail
    ) {}

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
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify-new-email',
            now()->addHours(24),
            [
                'id' => $notifiable->id,
                'token' => $notifiable->email_verification_token,
            ]
        );

        return (new MailMessage)
            ->subject('Verify New Email Address')
            ->line('Please click the button below to verify your new email address.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('This verification link will expire in 24 hours.')
            ->line('If you did not request this email change, no further action is required.');
    }
}
