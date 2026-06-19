<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Order;
use App\Models\BillingInvoice;
use App\Models\Subscription;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $now = Carbon::now();
        $thisMonthStart = $now->copy()->startOfMonth();
        $thisMonthEnd = $now->copy()->endOfMonth();

        // 1. KPI Calculations
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('subscription_status', 'active')->count();
        $trialTenants = Tenant::where('subscription_status', 'trial')->count();
        $expiredTenants = Tenant::where('subscription_status', 'expired')->count();
        $proSubscribers = Tenant::where('subscription_plan', 'pro')->count();
        
        // MRR Calculation (sum monthly prices of all active subscriptions)
        $mrr = Subscription::where('status', 'active')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->sum('plans.monthly_price');

        // Total orders this month (across all tenants)
        $totalOrdersThisMonth = Order::withoutGlobalScopes()
            ->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
            ->count();

        // Total Platform Revenue (all paid invoices)
        $totalPlatformRevenue = BillingInvoice::where('status', 'paid')->sum('amount');

        // 2. Charts Data
        // A. Tenant Growth (Last 30 Days)
        $tenantGrowth = Tenant::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $now->copy()->subDays(30))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->map(fn ($r) => ['date' => $r->date, 'count' => (int) $r->count]);

        // B. Free vs Pro Distribution
        $freeCount = Tenant::where('subscription_plan', 'free')->count();
        $proCount = Tenant::where('subscription_plan', 'pro')->count();
        $businessCount = Tenant::where('subscription_plan', 'business')->count();

        // C. Revenue Trend (Monthly for the last 6 months)
        $revenueTrend = BillingInvoice::selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as month, SUM(amount) as revenue")
            ->where('status', 'paid')
            ->where('paid_at', '>=', $now->copy()->subMonths(6))
            ->groupBy(DB::raw("DATE_FORMAT(paid_at, '%Y-%m')"))
            ->orderBy('month')
            ->get()
            ->map(fn ($r) => ['month' => $r->month, 'revenue' => (float) $r->revenue]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'kpis' => [
                    'total_tenants' => $totalTenants,
                    'active_tenants' => $activeTenants,
                    'trial_tenants' => $trialTenants,
                    'expired_tenants' => $expiredTenants,
                    'pro_subscribers' => $proSubscribers,
                    'mrr' => (float) $mrr,
                    'total_orders' => $totalOrdersThisMonth,
                    'total_revenue' => (float) $totalPlatformRevenue,
                ],
                'charts' => [
                    'tenant_growth' => $tenantGrowth,
                    'plan_distribution' => [
                        ['name' => 'Free', 'value' => $freeCount],
                        ['name' => 'Pro', 'value' => $proCount],
                        ['name' => 'Business', 'value' => $businessCount],
                    ],
                    'revenue_trend' => $revenueTrend,
                ],
            ],
        ]);
    }

    public function activities(Request $request): JsonResponse
    {
        $activities = [];

        // 1. New Tenant Registrations
        $newTenants = Tenant::latest()->take(10)->get();
        foreach ($newTenants as $t) {
            $activities[] = [
                'type' => 'registration',
                'title' => 'Tenant Baru Terdaftar',
                'description' => "Bisnis '{$t->name}' berhasil mendaftar (Paket: " . ucfirst($t->subscription_plan) . ").",
                'time' => $t->created_at->toIso8601String(),
                'icon' => '🏢',
            ];
        }

        // 2. Paid Invoices (Upgrades/Renewals)
        $paidInvoices = BillingInvoice::with(['tenant', 'subscription.plan'])
            ->where('status', 'paid')
            ->latest()
            ->take(10)
            ->get();

        foreach ($paidInvoices as $inv) {
            $planName = $inv->subscription?->plan?->name ?? 'Pro';
            $activities[] = [
                'type' => 'upgrade',
                'title' => 'Pembayaran Langganan',
                'description' => "Tenant '{$inv->tenant->name}' membayar invoice #{$inv->invoice_number} sebesar Rp " . number_format($inv->amount, 0, ',', '.') . " untuk Paket {$planName}.",
                'time' => $inv->paid_at ? $inv->paid_at->toIso8601String() : $inv->updated_at->toIso8601String(),
                'icon' => '💳',
            ];
        }

        // 3. Subscription Expired
        $expiredSubs = Subscription::with(['tenant', 'plan'])
            ->where('status', 'expired')
            ->latest()
            ->take(10)
            ->get();

        foreach ($expiredSubs as $sub) {
            $activities[] = [
                'type' => 'expiration',
                'title' => 'Masa Aktif Berakhir',
                'description' => "Paket {$sub->plan->name} untuk tenant '{$sub->tenant->name}' telah berakhir.",
                'time' => $sub->updated_at->toIso8601String(),
                'icon' => '⚠️',
            ];
        }

        // Sort combined activities by time descending
        usort($activities, fn ($a, $b) => strcmp($b['time'], $a['time']));

        // Return top 15 activities
        return response()->json([
            'status' => 'success',
            'data' => array_slice($activities, 0, 15),
        ]);
    }
}
