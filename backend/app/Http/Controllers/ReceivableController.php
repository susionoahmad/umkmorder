<?php

namespace App\Http\Controllers;

use App\Models\Receivable;
use App\Services\SubscriptionPlanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReceivableController extends Controller
{
    public function __construct(private SubscriptionPlanService $subscriptionPlans) {}

    public function index(Request $request): JsonResponse
    {
        if (!$this->subscriptionPlans->canAccessReceivables($request->user()->tenant)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Fitur Manajemen Piutang hanya tersedia untuk Paket Pro. Upgrade sekarang untuk mengakses fitur ini.',
                'upgrade_required' => true,
            ], 403);
        }

        $query = Receivable::with(['customer', 'order'])
            ->orderBy('due_date', 'asc');

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $receivables = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $receivables
        ]);
    }
}
