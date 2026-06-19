<?php

namespace App\Http\Controllers;

use App\Models\Receivable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReceivableController extends Controller
{
    public function index(Request $request): JsonResponse
    {
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
