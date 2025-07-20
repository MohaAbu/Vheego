<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AgencyStatusChanged extends Notification
{
    use Queueable;

    protected $agency;

    /**
     * Create a new notification instance.
     */
    public function __construct($agency)
    {
        $this->agency = $agency;
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
        $status = ucfirst($this->agency->verification_status);
        $subject = $status === 'Approved' ? 'Your Agency Has Been Approved' : 'Your Agency Application Was Rejected';
        $greeting = $status === 'Approved' ? 'Congratulations!' : 'We regret to inform you:';
        $line = $status === 'Approved'
            ? 'Your agency has been approved. You now have full access to car management features.'
            : 'Your agency application was rejected. Please review the feedback below.';
        return (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($line)
            ->line('Agency Name: ' . $this->agency->name)
            ->when($this->agency->admin_feedback, fn($msg) => $msg->line('Admin Feedback: ' . $this->agency->admin_feedback))
            ->action('Go to Dashboard', url('/dashboard/renter'))
            ->line('Thank you for using our platform!');
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
