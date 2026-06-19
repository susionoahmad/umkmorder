<?php

namespace App\Http\Controllers;

use App\Models\CatalogVisit;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Receivable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        // All queries auto-scoped to authenticated tenant via BelongsToTenant
        $totalVisitors     = CatalogVisit::withoutGlobalScopes()
            ->where('tenant_id', request()->user()->tenant_id)
            ->count();

        $draftOrders       = Order::where('status', 'draft')->count();

        $confirmedOrders   = Order::whereNotIn('status', ['draft', 'expired'])->count();

        $overdueReceivables = Receivable::where('status', 'overdue')->count();

        $conversionRate    = $totalVisitors > 0
            ? round(($confirmedOrders / $totalVisitors) * 100, 2)
            : 0;

        // 7-day trend data for charts
        $tenantId   = request()->user()->tenant_id;
        $days7      = collect(range(6, 0))->map(fn ($d) => Carbon::now()->subDays($d)->toDateString());

        $visitsByDay = CatalogVisit::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('visited_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(visited_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $ordersByDay = Order::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        // Receivables new vs collected per day
        $receivablesNewByDay = Receivable::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $paymentsCollectedByDay = Payment::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('payment_date', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $visitorsVsOrders = $days7->map(fn ($date) => [
            'date'    => $date,
            'visitors' => (int) ($visitsByDay[$date] ?? 0),
            'orders'  => (int) ($ordersByDay[$date] ?? 0),
        ])->values();

        $receivablesTrend = $days7->map(fn ($date) => [
            'date'      => $date,
            'new'       => (float) ($receivablesNewByDay[$date] ?? 0),
            'collected' => (float) ($paymentsCollectedByDay[$date] ?? 0),
        ])->values();

        // Recent orders for table
        $recentOrders = Order::with('customer')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'total_visitors'       => $totalVisitors,
                'draft_orders'         => $draftOrders,
                'confirmed_orders'     => $confirmedOrders,
                'overdue_receivables'  => $overdueReceivables,
                'conversion_rate'      => $conversionRate,
                'visitors_vs_orders'   => $visitorsVsOrders,
                'receivables_trend'    => $receivablesTrend,
                'recent_orders'        => $recentOrders,
            ]
        ]);
    }

    /**
     * Extended catalog analytics endpoint with date range filtering.
     */
    public function catalogAnalytics(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $range    = $request->query('range', '7days');
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        [$from, $to] = $this->resolveDateRange($range, $dateFrom, $dateTo);

        $days = collect();
        $current = $from->copy();
        while ($current->lte($to)) {
            $days->push($current->toDateString());
            $current->addDay();
        }

        $totalVisitors = CatalogVisit::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereBetween('visited_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->count();

        $draftOrders = Order::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('status', 'draft')
            ->whereBetween('created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->count();

        $confirmedOrders = Order::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereNotIn('status', ['draft', 'expired'])
            ->whereBetween('created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->count();

        $abandonedOrders = Order::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('status', 'expired')
            ->whereBetween('created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->count();

        $conversionRate = $totalVisitors > 0
            ? round(($confirmedOrders / $totalVisitors) * 100, 2)
            : 0;

        // Daily trend breakdown
        $visitsByDay = CatalogVisit::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereBetween('visited_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw('DATE(visited_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $ordersByDay = Order::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereNotIn('status', ['expired'])
            ->whereBetween('created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $confirmedByDay = Order::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereNotIn('status', ['draft', 'expired'])
            ->whereBetween('created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $abandonedByDay = Order::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('status', 'expired')
            ->whereBetween('created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $trend = $days->map(fn ($date) => [
            'date'      => $date,
            'visitors'  => (int) ($visitsByDay[$date] ?? 0),
            'orders'    => (int) ($ordersByDay[$date] ?? 0),
            'confirmed' => (int) ($confirmedByDay[$date] ?? 0),
            'abandoned' => (int) ($abandonedByDay[$date] ?? 0),
            'conversion'=> $visitsByDay[$date] ?? 0
                ? round((($confirmedByDay[$date] ?? 0) / $visitsByDay[$date]) * 100, 2)
                : 0,
        ])->values();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'range'            => $range,
                'from'             => $from->toDateString(),
                'to'               => $to->toDateString(),
                'total_visitors'   => $totalVisitors,
                'draft_orders'     => $draftOrders,
                'confirmed_orders' => $confirmedOrders,
                'abandoned_orders' => $abandonedOrders,
                'conversion_rate'  => $conversionRate,
                'trend'            => $trend,
            ]
        ]);
    }

    public function notifications(): JsonResponse
    {
        // Pesanan belum diproses: draft (dari katalog publik, belum disentuh owner) + new
        $unprocessedCount = Order::whereIn('status', ['draft', 'new'])->count();

        $latestUnprocessedOrders = Order::with('customer')
            ->whereIn('status', ['draft', 'new'])
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        // Pembayaran piutang yang baru lunas dalam 24 jam terakhir
        $recentPaidReceivables = Receivable::with(['order.customer'])
            ->where('status', 'paid')
            ->where('updated_at', '>=', Carbon::now()->subHours(24))
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'id'             => $r->id,
                'order_id'       => $r->order_id,
                'invoice_number' => $r->order->invoice_number ?? '-',
                'customer_name'  => $r->order->customer->name ?? '-',
                'paid_amount'    => (float) $r->paid_amount,
                'updated_at'     => $r->updated_at,
            ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'unprocessed_count'       => $unprocessedCount,
                'latest_unprocessed_orders' => $latestUnprocessedOrders,
                'recent_paid_receivables' => $recentPaidReceivables,
            ]
        ]);
    }

    private function resolveDateRange(string $range, ?string $dateFrom, ?string $dateTo): array
    {
        return match ($range) {
            'today'  => [Carbon::today(), Carbon::today()],
            '30days' => [Carbon::now()->subDays(29)->startOfDay(), Carbon::now()],
            'custom' => [
                Carbon::parse($dateFrom ?? now()->subDays(6)),
                Carbon::parse($dateTo ?? now()),
            ],
            default  => [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()], // 7days
        };
    }
}
