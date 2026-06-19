<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Receivable;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function sales(Request $request): JsonResponse
    {
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
