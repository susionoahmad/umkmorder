<?php

use Illuminate\Support\Facades\Schedule;

// Run overdue receivable check daily at midnight
Schedule::command('receivables:check-overdue')->daily();

// Run due date reminders daily at 8 AM
Schedule::command('receivables:send-reminders')->dailyAt('08:00');

// Cleanup old catalog visits weekly on Sundays
Schedule::command('catalog:cleanup-visits')->weeklyOn(0, '02:00');

// Auto-expire draft orders hourly
Schedule::command('orders:expire-drafts')->hourly();

// Check expired trials and subscriptions daily
Schedule::command('subscriptions:check-expired')->daily();
