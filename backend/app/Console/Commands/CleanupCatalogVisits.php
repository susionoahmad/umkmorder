<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CatalogVisit;
use Carbon\Carbon;

class CleanupCatalogVisits extends Command
{
    protected $signature = 'catalog:cleanup-visits';
    protected $description = 'Clean up catalog visits data older than 90 days';

    public function handle(): int
    {
        $cutoffDate = Carbon::now()->subDays(90)->toDateTimeString();
        
        $deleted = CatalogVisit::withoutGlobalScopes()
            ->where('visited_at', '<', $cutoffDate)
            ->delete();

        $this->info("Cleaned up {$deleted} catalog visits records older than 90 days.");
        return Command::SUCCESS;
    }
}
