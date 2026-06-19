<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Receivable;
use App\Services\SubscriptionPlanService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct(private SubscriptionPlanService $subscriptionPlans) {}

    public function sales(Request $request): JsonResponse
    {
        if (!$this->subscriptionPlans->canAccessReports($request->user()->tenant)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Laporan Lengkap hanya tersedia untuk Paket Pro. Upgrade sekarang untuk mengakses fitur ini.',
                'upgrade_required' => true,
            ], 403);
        }

        $method = $request->query('payment_method', 'all');

        if ($method === 'receivable_paid') {
            $receivables = Receivable::with(['order.customer'])
                ->where('status', 'paid')
                ->orderByDesc('updated_at')
                ->get();

            $rows = $receivables->map(fn ($receivable) => [
                'id' => 'receivable-' . $receivable->id,
                'date' => optional($receivable->updated_at)->toDateString(),
                'invoice_number' => $receivable->order?->invoice_number,
                'customer_name' => $receivable->customer?->name ?? $receivable->order?->customer?->name,
                'payment_method' => 'receivable_paid',
                'payment_method_label' => 'Piutang Lunas',
                'amount' => (float) $receivable->paid_amount,
                'order_id' => $receivable->order_id,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'rows' => $rows,
                    'summary' => [
                        'count' => $rows->count(),
                        'total' => (float) $rows->sum('amount'),
                    ],
                ],
            ]);
        }

        $paymentMethod = $method === 'credit_paid' ? 'credit' : $method;

        $query = Payment::with(['order.customer'])->orderByDesc('payment_date')->orderByDesc('id');
        if ($paymentMethod !== 'all') {
            $query->where('payment_method', $paymentMethod);
        }

        $payments = $query->get();

        $rows = $payments->map(fn ($payment) => [
            'id' => 'payment-' . $payment->id,
            'date' => optional($payment->payment_date)->toDateString(),
            'invoice_number' => $payment->order?->invoice_number,
            'customer_name' => $payment->order?->customer?->name,
            'payment_method' => $payment->payment_method,
            'payment_method_label' => $this->paymentMethodLabel($payment->payment_method),
            'amount' => (float) $payment->amount,
            'order_id' => $payment->order_id,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'rows' => $rows,
                'summary' => [
                    'count' => $rows->count(),
                    'total' => (float) $rows->sum('amount'),
                ],
            ],
        ]);
    }

    public function dailyTurnover(Request $request): JsonResponse
    {
        if (!$this->subscriptionPlans->canAccessReports($request->user()->tenant)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Laporan Omzet hanya tersedia untuk Paket Pro. Upgrade sekarang untuk mengakses fitur ini.',
                'upgrade_required' => true,
            ], 403);
        }

        $dateFrom = $request->query('date_from') ?: Carbon::now()->subDays(6)->toDateString();
        $dateTo = $request->query('date_to') ?: Carbon::now()->toDateString();

        $rows = Order::query()
            ->selectRaw('DATE(order_date) as date, COUNT(*) as orders_count, SUM(grand_total) as turnover')
            ->whereBetween('order_date', [$dateFrom, $dateTo])
            ->whereNotIn('status', ['draft', 'expired', 'cancelled'])
            ->groupBy(DB::raw('DATE(order_date)'))
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'orders_count' => (int) $row->orders_count,
                'turnover' => (float) $row->turnover,
            ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'rows' => $rows,
                'summary' => [
                    'orders_count' => (int) $rows->sum('orders_count'),
                    'turnover' => (float) $rows->sum('turnover'),
                ],
            ],
        ]);
    }

    public function export(Request $request)
    {
        if (!$this->subscriptionPlans->canAccessReports($request->user()->tenant)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Fitur Ekspor Laporan hanya tersedia untuk Paket Pro. Upgrade sekarang untuk mengakses fitur ini.',
                'upgrade_required' => true,
            ], 403);
        }

        $type = $request->query('type', 'sales');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="laporan_' . $type . '_' . now()->format('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($type, $request) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            if ($type === 'sales') {
                fputcsv($file, ['Tanggal', 'Invoice', 'Pelanggan', 'Metode Pembayaran', 'Nominal (Rp)']);

                $method = $request->query('payment_method', 'all');

                if ($method === 'receivable_paid') {
                    $receivables = Receivable::with(['order.customer'])
                        ->where('status', 'paid')
                        ->orderByDesc('updated_at')
                        ->get();

                    foreach ($receivables as $r) {
                        fputcsv($file, [
                            optional($r->updated_at)->toDateString(),
                            $r->order?->invoice_number ?: '-',
                            $r->customer?->name ?? $r->order?->customer?->name ?: '-',
                            'Piutang Lunas',
                            (float) $r->paid_amount
                        ]);
                    }
                } else {
                    $paymentMethod = $method === 'credit_paid' ? 'credit' : $method;
                    $query = Payment::with(['order.customer'])->orderByDesc('payment_date')->orderByDesc('id');
                    if ($paymentMethod !== 'all') {
                        $query->where('payment_method', $paymentMethod);
                    }
                    $payments = $query->get();

                    foreach ($payments as $p) {
                        fputcsv($file, [
                            optional($p->payment_date)->toDateString(),
                            $p->order?->invoice_number ?: '-',
                            $p->order?->customer?->name ?: '-',
                            $this->paymentMethodLabel($p->payment_method),
                            (float) $p->amount
                        ]);
                    }
                }
            } elseif ($type === 'receivables') {
                fputcsv($file, ['Invoice', 'Nama Pelanggan', 'WhatsApp', 'Total Tagihan (Rp)', 'Sisa Piutang (Rp)', 'Jatuh Tempo', 'Status']);

                $statusFilter = $request->query('status', 'all');
                $query = Receivable::with(['order.customer', 'customer'])->orderByDesc('due_date');
                if ($statusFilter !== 'all') {
                    $query->where('status', $statusFilter);
                }
                $receivables = $query->get();

                foreach ($receivables as $r) {
                    $statusLabel = match($r->status) {
                        'paid' => 'Lunas',
                        'partial' => 'Sebagian',
                        'overdue' => 'Jatuh Tempo',
                        'unpaid' => 'Belum Dibayar',
                        default => $r->status
                    };
                    fputcsv($file, [
                        $r->order?->invoice_number ?: '-',
                        $r->customer?->name ?: '-',
                        $r->customer?->whatsapp ?: '-',
                        (float) $r->total_amount,
                        (float) $r->remaining_amount,
                        $r->due_date ?: '-',
                        $statusLabel
                    ]);
                }
            } elseif ($type === 'turnover') {
                fputcsv($file, ['Tanggal', 'Jumlah Pesanan', 'Omzet (Rp)']);

                $dateFrom = $request->query('date_from') ?: Carbon::now()->subDays(6)->toDateString();
                $dateTo = $request->query('date_to') ?: Carbon::now()->toDateString();

                $rows = Order::query()
                    ->selectRaw('DATE(order_date) as date, COUNT(*) as orders_count, SUM(grand_total) as turnover')
                    ->whereBetween('order_date', [$dateFrom, $dateTo])
                    ->whereNotIn('status', ['draft', 'expired', 'cancelled'])
                    ->groupBy(DB::raw('DATE(order_date)'))
                    ->orderBy('date')
                    ->get();

                foreach ($rows as $r) {
                    fputcsv($file, [
                        $r->date,
                        (int) $r->orders_count,
                        (float) $r->turnover
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function paymentMethodLabel(?string $method): string
    {
        return match ($method) {
            'cash' => 'Tunai',
            'transfer' => 'Transfer',
            'qris' => 'QRIS',
            'credit' => 'Kredit Lunas',
            default => '-',
        };
    }
}
