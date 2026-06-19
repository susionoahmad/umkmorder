<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function store(Request $request, $orderId, PaymentService $paymentService): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string'
        ]);

        try {
            $data = $request->all();
            $data['order_id'] = $orderId;

            $payment = $paymentService->recordPayment($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Pembayaran berhasil dicatat',
                'data' => $payment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
