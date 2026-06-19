<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index(): JsonResponse
    {
        $plans = Plan::orderBy('monthly_price')->get();
        return response()->json([
            'status' => 'success',
            'data' => $plans,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'monthly_price' => 'required|numeric|min:0',
            'yearly_price' => 'required|numeric|min:0',
            'max_products' => 'required|integer',
            'max_orders' => 'required|integer',
            'trial_days' => 'required|integer|min:0',
            'features_json' => 'nullable|array',
            'is_active' => 'required|boolean',
        ]);

        $slug = Str::slug($request->name);

        // Check if slug is unique
        $originalSlug = $slug;
        $count = 1;
        while (Plan::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $plan = Plan::create(array_merge($request->all(), ['slug' => $slug]));

        return response()->json([
            'status' => 'success',
            'message' => 'Paket baru berhasil dibuat.',
            'data' => $plan,
        ], 211); // Standard created status
    }

    public function show(Plan $plan): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $plan,
        ]);
    }

    public function update(Request $request, Plan $plan): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'monthly_price' => 'required|numeric|min:0',
            'yearly_price' => 'required|numeric|min:0',
            'max_products' => 'required|integer',
            'max_orders' => 'required|integer',
            'trial_days' => 'required|integer|min:0',
            'features_json' => 'nullable|array',
            'is_active' => 'required|boolean',
        ]);

        $plan->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Paket berhasil diperbarui.',
            'data' => $plan,
        ]);
    }

    public function destroy(Plan $plan): JsonResponse
    {
        $plan->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Paket berhasil dihapus.',
        ]);
    }
}
