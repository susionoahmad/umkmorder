<?php

namespace App\Notifications;

use App\Models\Receivable;
use App\Jobs\SendReminderWhatsAppJob;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReceivableReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $receivable;

    public function __construct(Receivable $receivable)
    {
        $this->receivable = $receivable;
    }

    public function via(object $notifiable): array
    {
        return [\App\Notifications\Channels\WhatsAppChannel::class];
    }

    public function toWhatsApp(object $notifiable): void
    {
        SendReminderWhatsAppJob::dispatch($this->receivable);
    }
}
