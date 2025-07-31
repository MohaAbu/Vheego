<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Reservation;

class ReservationCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    public $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Car Reservation has been Cancelled')
            ->line('We regret to inform you that your car reservation has been cancelled.')
            ->line('Car: ' . $this->reservation->car->make . ' ' . $this->reservation->car->model)
            ->line('Agency: ' . $this->reservation->car->agency->name)
            ->line('Dates: ' . $this->reservation->start_date . ' to ' . $this->reservation->end_date)
            ->line('If you have any questions, please contact the agency directly.')
            ->action('View Reservations', url('/reservations'))
            ->line('Thank you for using our service!');
    }
}