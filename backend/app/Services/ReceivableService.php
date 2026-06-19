<?php

namespace App\Services;

use App\Models\Receivable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReceivableService
{
    public function scanAndMarkOverdue(): int
    {
        $today = Carbon::now()->toDateString();
        
        return Receivable::withoutGlobalScopes()
            ->where('due_date', '<', $today)
            ->whereIn('status', ['unpaid', 'partial'])
            ->update(['status' => 'overdue']);
    }

    public function getAgingReceivables(int $tenantId)
    {
        return Receivable::where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->orderBy('due_date', 'asc')
            ->get();
    }
}
