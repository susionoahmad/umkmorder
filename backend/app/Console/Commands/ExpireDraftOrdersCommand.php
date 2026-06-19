<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
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
    public function handle()
    {
        $count = Order::withoutGlobalScopes()
            ->where('status', 'draft')
            ->where('expires_at', '<', Carbon::now())
            ->update(['status' => 'expired']);

        $this->info("Expired {$count} draft orders successfully.");
    }
}
