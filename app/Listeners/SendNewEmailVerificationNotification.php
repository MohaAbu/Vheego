<?php

namespace App\Listeners;

use App\Events\PendingEmailChange;
use App\Notifications\VerifyNewEmail;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewEmailVerificationNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(PendingEmailChange $event): void
    {
        $event->user->notify(new VerifyNewEmail($event->newEmail));
    }
}
