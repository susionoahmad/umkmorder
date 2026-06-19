<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendInvoiceWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(WhatsAppService $whatsappService): void
    {
        $order = $this->order->load(['customer', 'tenant']);
        
        $message = "Halo {$order->customer->name}\n\n";
        $message .= "Terima kasih atas pesanan Anda di {$order->tenant->name}.\n";
        $message .= "Invoice: {$order->invoice_number}\n";
        $message .= "Total Tagihan: Rp " . number_format($order->grand_total, 0, ',', '.') . "\n\n";
        $message .= "Terima kasih.";

        $whatsappService->send($order->tenant, $order->customer->whatsapp, $message, $order->customer_id);
    }
}
