<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReceivableService;

class CheckOverdueReceivables extends Command
{
    protected $signature = 'receivables:check-overdue';
    protected $description = 'Scan receivables past their due dates and mark them as overdue';

    public function handle(ReceivableService $receivableService): int
    {
        $this->info('Scanning for overdue receivables...');
        $updatedCount = $receivableService->scanAndMarkOverdue();
        $this->info("Completed. {$updatedCount} receivables marked as overdue.");
        return Command::SUCCESS;
    }
}
