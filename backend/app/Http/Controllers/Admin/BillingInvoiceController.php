<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillingInvoice;
use App\Models\Tenant;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BillingInvoiceController extends Controller
{
    public function index(): JsonResponse
    {
        $invoices = BillingInvoice::with(['tenant', 'subscription.plan'])->latest()->get();
        return response()->json([
            'status' => 'success',
            'data' => $invoices,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'subscription_id' => 'nullable|exists:subscriptions,id',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string|in:paid,pending,overdue,cancelled',
            'due_date' => 'required|date',
        ]);

        $invoiceNumber = 'INV-' . Carbon::now()->format('Ymd') . '-' . mt_rand(1000, 9999);

        $payload = array_merge($request->all(), [
            'invoice_number' => $invoiceNumber,
            'paid_at' => $request->status === 'paid' ? Carbon::now() : null,
        ]);

        $invoice = BillingInvoice::create($payload);

        // If marked as paid, let's automatically activate/renew the subscription!
        if ($invoice->status === 'paid' && $invoice->subscription_id) {
            $sub = Subscription::find($invoice->subscription_id);
            if ($sub) {
                $sub->update(['status' => 'active']);
                if ($sub->tenant) {
                    $sub->tenant->update([
                        'subscription_status' => 'active',
                        'subscription_plan' => $sub->plan->slug,
                    ]);
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice baru berhasil diterbitkan.',
            'data' => $invoice->load(['tenant', 'subscription.plan']),
        ]);
    }

    public function update(Request $request, BillingInvoice $invoice): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:paid,pending,overdue,cancelled',
            'due_date' => 'required|date',
        ]);

        $statusChangedToPaid = ($request->status === 'paid' && $invoice->status !== 'paid');

        $invoice->update([
            'status' => $request->status,
            'due_date' => $request->due_date,
            'paid_at' => $request->status === 'paid' ? ($invoice->paid_at ?: Carbon::now()) : null,
        ]);

        // If status changed to paid, activate the associated subscription
        if ($statusChangedToPaid && $invoice->subscription_id) {
            $sub = Subscription::find($invoice->subscription_id);
            if ($sub) {
                $sub->update(['status' => 'active']);
                if ($sub->tenant) {
                    $sub->tenant->update([
                        'subscription_status' => 'active',
                        'subscription_plan' => $sub->plan->slug,
                    ]);
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Status invoice berhasil diperbarui.',
            'data' => $invoice->load(['tenant', 'subscription.plan']),
        ]);
    }
}
