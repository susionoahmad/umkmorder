<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Subscription;
use Carbon\Carbon;

class CheckExpiredSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-expired';
    protected $description = 'Scan expired trials and active subscriptions, marking them as expired';

    public function handle(): int
    {
        $now = Carbon::now();

        // 1. Expire trials
        $expiredTrials = Tenant::where('subscription_status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', $now)
            ->get();

        foreach ($expiredTrials as $tenant) {
            $tenant->update([
                'subscription_status' => 'expired',
                'subscription_plan' => 'free', // downgrade to free
            ]);
            $this->info("Trial expired for tenant: {$tenant->name}");
        }

        // 2. Expire active subscriptions
        $expiredSubscriptions = Subscription::where('status', 'active')
            ->whereNotNull('expired_at')
            ->where('expired_at', '<', $now)
            ->get();

        foreach ($expiredSubscriptions as $sub) {
            $sub->update(['status' => 'expired']);
            
            // Downgrade tenant to free/expired
            if ($sub->tenant) {
                $sub->tenant->update([
                    'subscription_status' => 'expired',
                    'subscription_plan' => 'free',
                ]);
            }
            $this->info("Subscription ID {$sub->id} expired for tenant: " . ($sub->tenant->name ?? 'Unknown'));
        }

        $this->info('Completed scanning expired subscriptions and trials.');
        return Command::SUCCESS;
    }
}
