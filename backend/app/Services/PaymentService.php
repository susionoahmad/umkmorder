<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Receivable;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PaymentService
{
    public function recordPayment(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            // ⚠️ PERBAIKAN IDOR: Validasi kepemilikan tenant
            // Cari order yang ID-nya sesuai DAN tenant_id-nya sama dengan user yang login
            $order = Order::where('id', $data['order_id'])
                ->where('tenant_id', auth()->user()->tenant_id)
                ->first();

            // Jika order tidak ditemukan atau bukan milik tenant tersebut, lempar error
            if (!$order) {
                throw new ModelNotFoundException("Order tidak ditemukan atau Anda tidak memiliki akses.");
            }

            // 1. Create Payment
            $payment = Payment::create([
                'tenant_id' => $order->tenant_id, // Sekarang dijamin aman karena sudah divalidasi di atas
                'order_id' => $order->id,
                'payment_date' => $data['payment_date'] ?? Carbon::now()->toDateString(),
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'notes' => $data['notes'] ?? null,
            ]);

            // 2. Update Receivable if exists
            $receivable = Receivable::where('order_id', $order->id)->first();
            if ($receivable) {
                $receivable->paid_amount += $payment->amount;
                $receivable->remaining_amount = max(0, $receivable->total_amount - $receivable->paid_amount);

                if ($receivable->remaining_amount <= 0) {
                    $receivable->status = 'paid';
                } else {
                    $receivable->status = 'partial';
                }

                $receivable->save();
            }

            return $payment;
        });
    }
}