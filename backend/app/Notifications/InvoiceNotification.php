<?php

namespace App\Notifications;

use App\Models\Order;
use App\Jobs\SendInvoiceWhatsAppJob;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class InvoiceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via(object $notifiable): array
    {
        return [\App\Notifications\Channels\WhatsAppChannel::class];
    }

    public function toWhatsApp(object $notifiable): void
    {
        // Enqueue sending via background worker
        SendInvoiceWhatsAppJob::dispatch($this->order);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Invoice untuk Pesanan #' . $this->order->invoice_number)
            ->line('Terima kasih atas pembelian Anda.')
            ->action('Lihat Invoice', url('/orders/' . $this->order->id))
            ->line('Terima kasih.');
    }
}
