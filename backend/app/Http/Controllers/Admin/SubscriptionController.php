<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    public function index(): JsonResponse
    {
        $subscriptions = Subscription::with(['tenant', 'plan'])->latest()->get();
        return response()->json([
            'status' => 'success',
            'data' => $subscriptions,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'plan_id' => 'required|exists:plans,id',
            'started_at' => 'required|date',
            'expired_at' => 'nullable|date',
            'status' => 'required|string|in:trial,active,expired,suspended,pending',
        ]);

        $tenant = Tenant::findOrFail($request->tenant_id);
        $plan = Plan::findOrFail($request->plan_id);

        // Deactivate old active subscriptions for this tenant
        Subscription::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->update(['status' => 'expired']);

        // Create new subscription
        $subscription = Subscription::create($request->all());

        // Update Tenant plan/status fields
        $tenant->update([
            'subscription_plan' => $plan->slug,
            'subscription_status' => $request->status,
            'trial_ends_at' => $request->status === 'trial' ? $request->expired_at : null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Langganan berhasil ditambahkan.',
            'data' => $subscription->load(['tenant', 'plan']),
        ]);
    }

    public function update(Request $request, Subscription $subscription): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'started_at' => 'required|date',
            'expired_at' => 'nullable|date',
            'status' => 'required|string|in:trial,active,expired,suspended,pending',
        ]);

        $plan = Plan::findOrFail($request->plan_id);

        $subscription->update($request->all());

        // Update Tenant fields if subscription is active or trial
        if (in_array($request->status, ['active', 'trial'])) {
            $subscription->tenant->update([
                'subscription_plan' => $plan->slug,
                'subscription_status' => $request->status,
                'trial_ends_at' => $request->status === 'trial' ? $request->expired_at : null,
            ]);
        } else {
            $subscription->tenant->update([
                'subscription_status' => $request->status,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Langganan berhasil diperbarui.',
            'data' => $subscription->load(['tenant', 'plan']),
        ]);
    }
}
