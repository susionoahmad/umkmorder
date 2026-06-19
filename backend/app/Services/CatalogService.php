<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Product;
use App\Models\CatalogVisit;
use Carbon\Carbon;

class CatalogService
{
    public function resolveTenant(string $slug): ?Tenant
    {
        return Tenant::with('catalogSetting')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    public function getActiveProducts(int $tenantId)
    {
        // Disable global scope temporarily or query explicitly
        return Product::withoutGlobalScopes()
            ->with(['priceTiers' => fn($q) => $q->where('is_active', true)->orderBy('min_qty')])
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();
    }

    public function recordVisit(int $tenantId, string $ip, ?string $userAgent): CatalogVisit
    {
        return CatalogVisit::withoutGlobalScopes()->create([
            'tenant_id' => $tenantId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'visited_at' => Carbon::now()
        ]);
    }
}
