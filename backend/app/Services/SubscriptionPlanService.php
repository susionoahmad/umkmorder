<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Tenant;
use Carbon\Carbon;

class SubscriptionPlanService
{
    public const FREE_PRODUCT_LIMIT = 20;
    public const FREE_ORDER_LIMIT = 100;
    public const WARNING_THRESHOLD = 80;

    private const CONFIRMED_ORDER_STATUSES = ['new', 'processing', 'shipped', 'completed'];

    public function usageForTenant(Tenant $tenant): array
    {
        $plan = $tenant->subscription_plan ?: 'free';
        $activeProducts = $this->activeProductCount($tenant->id);
        $monthlyOrders = $this->monthlyConfirmedOrderCount($tenant->id);

        return [
            'plan' => $plan,
            'plan_label' => $this->planLabel($plan),
            'active_products' => [
                'used' => $activeProducts,
                'limit' => $this->productLimit($plan),
                'percentage' => $this->percentage($activeProducts, $this->productLimit($plan)),
                'warning' => $this->isNearLimit($activeProducts, $this->productLimit($plan)),
            ],
            'monthly_orders' => [
                'used' => $monthlyOrders,
                'limit' => $this->orderLimit($plan),
                'percentage' => $this->percentage($monthlyOrders, $this->orderLimit($plan)),
                'warning' => $this->isNearLimit($monthlyOrders, $this->orderLimit($plan)),
            ],
            'warning_message' => $this->hasWarning($activeProducts, $monthlyOrders, $plan)
                ? 'Anda hampir mencapai batas paket Gratis. Upgrade ke Paket Pro untuk produk dan order tanpa batas.'
                : null,
        ];
    }

    public function canActivateProduct(Tenant $tenant, ?int $exceptProductId = null): bool
    {
        $limit = $this->productLimit($tenant->subscription_plan ?: 'free');

        if ($limit === null) {
            return true;
        }

        return $this->activeProductCount($tenant->id, $exceptProductId) < $limit;
    }

    public function canAddConfirmedOrder(Tenant $tenant): bool
    {
        $limit = $this->orderLimit($tenant->subscription_plan ?: 'free');

        if ($limit === null) {
            return true;
        }

        return $this->monthlyConfirmedOrderCount($tenant->id) < $limit;
    }

    public function canAccessReceivables(Tenant $tenant): bool
    {
        return ($tenant->subscription_plan ?? 'free') !== 'free';
    }

    public function canAccessReports(Tenant $tenant): bool
    {
        return ($tenant->subscription_plan ?? 'free') !== 'free';
    }

    public function canAccessQrCode(Tenant $tenant): bool
    {
        return ($tenant->subscription_plan ?? 'free') !== 'free';
    }

    public function canAccessCatalogAnalytics(Tenant $tenant): bool
    {
        return ($tenant->subscription_plan ?? 'free') !== 'free';
    }

    public function isConfirmedOrderStatus(string $status): bool
    {
        return in_array($status, self::CONFIRMED_ORDER_STATUSES, true);
    }

    private function activeProductCount(int $tenantId, ?int $exceptProductId = null): int
    {
        return Product::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->when($exceptProductId, fn ($query) => $query->where('id', '!=', $exceptProductId))
            ->count();
    }

    private function monthlyConfirmedOrderCount(int $tenantId): int
    {
        return Order::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', self::CONFIRMED_ORDER_STATUSES)
            ->whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->count();
    }

    private function planLabel(string $plan): string
    {
        return match ($plan) {
            'pro' => 'Pro',
            'business' => 'Bisnis',
            default => 'Gratis',
        };
    }

    private function productLimit(string $plan): ?int
    {
        return $plan === 'free' ? self::FREE_PRODUCT_LIMIT : null;
    }

    private function orderLimit(string $plan): ?int
    {
        return $plan === 'free' ? self::FREE_ORDER_LIMIT : null;
    }

    private function percentage(int $used, ?int $limit): ?int
    {
        if ($limit === null || $limit === 0) {
            return null;
        }

        return min(100, (int) floor(($used / $limit) * 100));
    }

    private function isNearLimit(int $used, ?int $limit): bool
    {
        if ($limit === null || $limit === 0) {
            return false;
        }

        return ($used / $limit) * 100 >= self::WARNING_THRESHOLD;
    }

    private function hasWarning(int $activeProducts, int $monthlyOrders, string $plan): bool
    {
        return $plan === 'free'
            && (
                $this->isNearLimit($activeProducts, self::FREE_PRODUCT_LIMIT)
                || $this->isNearLimit($monthlyOrders, self::FREE_ORDER_LIMIT)
            );
    }
}
