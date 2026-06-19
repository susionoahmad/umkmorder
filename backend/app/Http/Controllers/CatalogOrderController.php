<?php

namespace App\Http\Controllers;

use App\Services\CatalogService;
use App\Services\DraftOrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CatalogOrderController extends Controller
{
    protected $catalogService;
    protected $draftOrderService;

    public function __construct(CatalogService $catalogService, DraftOrderService $draftOrderService)
    {
        $this->catalogService   = $catalogService;
        $this->draftOrderService = $draftOrderService;
    }

    public function store(Request $request, string $slug): JsonResponse
    {
        $tenant = $this->catalogService->resolveTenant($slug);

        if (!$tenant || !($tenant->catalogSetting?->catalog_enabled ?? true)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Catalog not found or disabled'
            ], 404);
        }

        // Validate payload
        $validator = Validator::make($request->all(), [
            'customer_name'     => 'required|string|max:150',
            'customer_whatsapp' => 'required|string|max:20',
            'customer_address'  => 'nullable|string',
            'location_link'     => 'nullable|string',
            'notes'             => 'nullable|string',
            'order_type'        => 'nullable|in:delivery,pickup',
            'payment_preference'=> 'required|in:cash,transfer,qris,tempo,credit',
            // Shipping fields
            'customer_lat'      => 'nullable|numeric|between:-90,90',
            'customer_lng'      => 'nullable|numeric|between:-180,180',
            'shipping_zone'     => 'nullable|string|max:100',
            // Items
            'items'             => 'required|array|min:1',
            'items.*.product_id'=> 'required|integer',
            'items.*.quantity'  => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            // Bind resolved tenant to singleton to allow multi-tenant scoping in traits
            app()->instance('resolved_tenant_id', $tenant->id);

            // Create draft order
            $order = $this->draftOrderService->createDraftOrder($tenant, $request->all());

            // Load relations needed for response and WhatsApp message
            $order->load(['customer', 'items.product']);

            // Generate WhatsApp redirect text & URL
            $waUrl = $this->draftOrderService->generateWhatsAppRedirectUrl($tenant, $order);

            // Build item summary for frontend display
            $itemSummary = $order->items->map(fn ($item) => [
                'name'     => $item->product->name ?? '-',
                'quantity' => $item->quantity,
                'price'    => (float) $item->price,
                'total'    => (float) $item->total,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Order draft placed successfully',
                'data'    => [
                'order_id'                => $order->id,
                'invoice_number'          => $order->invoice_number,
                'status'                  => $order->status,
                'payment_preference'      => $order->payment_preference,
                'customer_name'           => $order->customer->name,
                    'subtotal'                => (float) $order->subtotal,
                    'shipping_cost'           => (float) $order->shipping_cost,
                    'shipping_distance_km'    => $order->shipping_distance_km ? (float) $order->shipping_distance_km : null,
                    'shipping_zone'           => $order->shipping_zone,
                    'grand_total'             => (float) $order->grand_total,
                    'items'                   => $itemSummary,
                    'whatsapp_redirect_url'   => $waUrl,
                    'auto_whatsapp_redirect'  => (bool) ($tenant->catalogSetting?->auto_whatsapp_redirect ?? false),
                ]
            ], 201);
        } catch (\Exception $exception) {
            return response()->json([
                'status'  => 'error',
                'message' => $exception->getMessage()
            ], 500);
        }
    }
}
