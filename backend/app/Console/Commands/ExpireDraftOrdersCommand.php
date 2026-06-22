<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\OrderService;
use Carbon\Carbon;

class ExpireDraftOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire-drafts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-expire draft orders that have passed their expiration time';

    /**
     * Execute the console command.
     */
    public function handle(OrderService $orderService)
    {
        $expiredOrders = Order::withoutGlobalScopes()
            ->where('status', 'draft')
            ->where('expires_at', '<', Carbon::now())
            ->get();

        $count = 0;
        foreach ($expiredOrders as $order) {
            try {
                $orderService->updateStatus($order->id, 'expired', null);
                $count++;
            } catch (\Exception $e) {
                $this->error("Failed to expire order #{$order->id}: " . $e->getMessage());
            }
        }

        $this->info("Expired {$count} draft orders successfully.");
    }
}
