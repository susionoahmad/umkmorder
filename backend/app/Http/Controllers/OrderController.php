<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(): JsonResponse
    {
        // Automatically scoped to authenticated tenant
        $orders = Order::with(['customer', 'receivable'])->orderBy('id', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    public function show($id): JsonResponse
    {
        // Automatically scoped to authenticated tenant
        $order = Order::with(['customer', 'items.product', 'receivable', 'payments'])->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $order
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:draft,new,processing,shipped,completed,cancelled',
            'payment_status' => 'nullable|in:paid,piutang',
            'payment_method' => 'nullable|required_if:payment_status,paid|string',
            'payment_due_date' => 'nullable|date',
        ]);

        try {
            $order = $this->orderService->updateStatus(
                $id,
                $request->status,
                $request->user()->id,
                $request->payment_status,
                $request->payment_method,
                $request->payment_due_date
            );
            $order->load(['customer', 'receivable']);

            return response()->json([
                'status' => 'success',
                'message' => 'Status pesanan berhasil diperbarui',
                'data' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
