<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAgencySubmitted extends Notification
{
    use Queueable;

    protected $agency;
    protected $renter;

    /**
     * Create a new notification instance.
     */
    public function __construct($agency, $renter)
    {
        $this->agency = $agency;
        $this->renter = $renter;
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
        return (new MailMessage)
            ->subject('New Agency Registration Submitted')
            ->greeting('Hello Admin,')
            ->line('A new agency registration has been submitted and is pending your review.')
            ->line('Agency Name: ' . $this->agency->name)
            ->line('Renter Name: ' . $this->renter->name)
            ->line('Renter Email: ' . $this->renter->email)
            ->line('Contact Email: ' . $this->agency->contact_email)
            ->line('Contact Phone: ' . $this->agency->contact_phone)
            ->line('Address: ' . $this->agency->address)
            ->line('Description: ' . $this->agency->description)
            ->action('Review Agencies', url('/dashboard/admin'))
            ->line('Thank you for helping keep the platform high quality!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
