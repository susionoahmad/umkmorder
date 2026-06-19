<?php

namespace App\Http\Controllers;

use App\Services\CatalogService;
use App\Services\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ShippingController extends Controller
{
    public function __construct(
        private CatalogService  $catalogService,
        private ShippingService $shippingService
    ) {}

    /**
     * Calculate shipping cost preview for a given catalog slug.
     * Used by frontend checkout page to show real-time shipping estimate.
     *
     * POST /api/public/catalog/{slug}/calculate-shipping
     *
     * Body:
     *   customer_lat  float|null
     *   customer_lng  float|null
     *   zone_name     string|null
     *   order_type    string (delivery|pickup)
     */
    public function calculate(Request $request, string $slug): JsonResponse
    {
        $tenant = $this->catalogService->resolveTenant($slug);

        if (!$tenant || !($tenant->catalogSetting?->catalog_enabled ?? true)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Catalog not found or disabled',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'customer_lat' => 'nullable|numeric|between:-90,90',
            'customer_lng' => 'nullable|numeric|between:-180,180',
            'zone_name'    => 'nullable|string|max:100',
            'order_type'   => 'required|in:delivery,pickup',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $result = $this->shippingService->calculate(
            tenant:      $tenant,
            orderType:   $data['order_type'],
            customerLat: isset($data['customer_lat']) ? (float) $data['customer_lat'] : null,
            customerLng: isset($data['customer_lng']) ? (float) $data['customer_lng'] : null,
            zoneName:    $data['zone_name'] ?? null,
        );

        // Also include the shipping config so frontend can display zone list / brackets
        $setting = $tenant->catalogSetting;

        return response()->json([
            'status' => 'success',
            'data'   => [
                'mode'              => $result['mode'],
                'cost'              => $result['cost'],
                'distance_km'       => $result['distance_km'],
                'zone'              => $result['zone'],
                'shipping_mode'     => $setting?->shipping_mode ?? 'none',
                'shipping_zones'    => $setting?->shipping_zones ?? [],
                'shipping_distances'=> $setting?->shipping_distances ?? [],
                'store_lat'         => $setting?->store_latitude,
                'store_lng'         => $setting?->store_longitude,
                'store_label'       => $setting?->store_location_label,
            ],
        ]);
    }
}
