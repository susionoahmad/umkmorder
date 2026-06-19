<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;

class WhatsAppChannel
{
    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (method_exists($notification, 'toWhatsApp')) {
            $notification->toWhatsApp($notifiable);
        }
    }
}
