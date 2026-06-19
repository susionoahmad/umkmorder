<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Receivable;
use App\Notifications\ReceivableReminderNotification;
use Carbon\Carbon;

class SendDueDateReminders extends Command
{
    protected $signature = 'receivables:send-reminders';
    protected $description = 'Send WhatsApp payment reminders for receivables due today';

    public function handle(): int
    {
        $today = Carbon::now()->toDateString();
        
        $receivables = Receivable::withoutGlobalScopes()
            ->where('due_date', $today)
            ->whereIn('status', ['unpaid', 'partial'])
            ->get();

        $this->info("Found {$receivables->count()} receivables due today. Sending reminders...");

        foreach ($receivables as $receivable) {
            // Send reminder
            $receivable->customer->notify(new ReceivableReminderNotification($receivable));
        }

        $this->info('Reminders enqueued successfully.');
        return Command::SUCCESS;
    }
}
