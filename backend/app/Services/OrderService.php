<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Receivable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private SubscriptionPlanService $subscriptionPlans,
    ) {}

    public function updateStatus($orderId, $newStatus, $userId, $paymentStatus = null, $paymentMethod = null, $paymentDueDate = null)
    {
        // Gunakan DB Transaction agar jika gagal, data tidak setengah-setengah tersimpan
        return DB::transaction(function () use ($orderId, $newStatus, $userId, $paymentStatus, $paymentMethod, $paymentDueDate) {
            $order = Order::findOrFail($orderId);
            $willConsumeOrderSlot = !$this->subscriptionPlans->isConfirmedOrderStatus($order->status)
                && $this->subscriptionPlans->isConfirmedOrderStatus($newStatus);

            if ($willConsumeOrderSlot && !$this->subscriptionPlans->canAddConfirmedOrder($order->tenant)) {
                throw new \Exception('Batas 100 order per bulan untuk Paket Gratis sudah tercapai. Upgrade ke Paket Pro untuk order tanpa batas.');
            }

            // Catat log perubahan status jika statusnya berubah
            if ($order->status !== $newStatus) {
                OrderStatusLog::create([
                    'order_id' => $order->id,
                    'old_status' => $order->status,
                    'new_status' => $newStatus,
                    'created_by' => $userId,
                    'created_at' => now(),
                ]);
            }

            $order->status = $newStatus;
            if ($paymentDueDate) {
                $order->payment_due_date = $paymentDueDate;
            }
            $order->save();

            // UI mengirim "piutang", sedangkan enum database memakai status receivable.
            if ($paymentStatus === 'piutang') {
                $receivable = $order->receivable()->first();

                if (!$receivable) {
                    Receivable::create([
                        'tenant_id' => $order->tenant_id,
                        'order_id' => $order->id,
                        'customer_id' => $order->customer_id,
                        'total_amount' => $order->grand_total,
                        'paid_amount' => 0,
                        'remaining_amount' => $order->grand_total,
                        'due_date' => $paymentDueDate ?: Carbon::now()->addDays(7)->toDateString(),
                        'status' => 'unpaid',
                    ]);
                } else {
                    $remainingAmount = max(0, $order->grand_total - $receivable->paid_amount);

                    $receivable->update([
                        'total_amount' => $order->grand_total,
                        'remaining_amount' => $remainingAmount,
                        'due_date' => $paymentDueDate ?: $receivable->due_date,
                        'status' => $remainingAmount <= 0 ? 'paid' : ($receivable->paid_amount > 0 ? 'partial' : 'unpaid'),
                    ]);
                }
            }

            if ($paymentStatus === 'paid') {
                $receivable = $order->receivable()->first();
                $amountToPay = $receivable ? $receivable->remaining_amount : $order->grand_total;

                if ($receivable) {
                    $receivable->update([
                        'paid_amount' => $order->grand_total,
                        'remaining_amount' => 0,
                        'status' => 'paid',
                    ]);
                }

                if ($paymentMethod && $amountToPay > 0) {
                    \App\Models\Payment::create([
                        'tenant_id' => $order->tenant_id,
                        'order_id' => $order->id,
                        'payment_date' => Carbon::now()->toDateString(),
                        'amount' => $amountToPay,
                        'payment_method' => $paymentMethod,
                    ]);
                }
            }

            return $order;
        });
    }
}
