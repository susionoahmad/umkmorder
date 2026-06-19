<?php

namespace App\Jobs;

use App\Models\Receivable;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendReminderWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $receivable;

    public function __construct(Receivable $receivable)
    {
        $this->receivable = $receivable;
    }

    public function handle(WhatsAppService $whatsappService): void
    {
        $receivable = $this->receivable->load(['customer', 'order', 'tenant']);

        $message = "Halo {$receivable->customer->name}\n\n";
        $message .= "Tagihan invoice {$receivable->order->invoice_number}\n";
        $message .= "sebesar Rp " . number_format($receivable->remaining_amount, 0, ',', '.') . "\n\n";
        $message .= "Jatuh tempo pada {$receivable->due_date->format('d-m-Y')}\n\n";
        $message .= "Terima kasih.";

        $whatsappService->send($receivable->tenant, $receivable->customer->whatsapp, $message, $receivable->customer_id);
    }
}
