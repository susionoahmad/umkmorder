<?php

namespace App\Http\Controllers;

use App\Services\CatalogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PublicCatalogController extends Controller
{
    protected $catalogService;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $tenant = $this->catalogService->resolveTenant($slug);

        if (!$tenant || !($tenant->catalogSetting?->catalog_enabled ?? true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Catalog not found or disabled'
            ], 404);
        }

        // Record visit in background
        $this->catalogService->recordVisit(
            $tenant->id,
            $request->ip(),
            $request->userAgent()
        );

        $products = $this->catalogService->getActiveProducts($tenant->id);

        $publicSettings = $tenant->catalogSetting ? $tenant->catalogSetting->toArray() : [];
        unset($publicSettings['bank_transfer_info'], $publicSettings['qris_info'], $publicSettings['qris_image_url']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'tenant' => [
                    'name'    => $tenant->name,
                    'slug'    => $tenant->slug,
                    'logo'    => $tenant->logo,
                    'phone'   => $tenant->phone,
                    'address' => $tenant->address,
                    'settings' => array_merge(
                        $publicSettings,
                        [
                            // Expose shipping config for checkout UI
                            'shipping_mode'      => $tenant->catalogSetting?->shipping_mode ?? 'none',
                            'shipping_zones'     => $tenant->catalogSetting?->shipping_zones ?? [],
                            'shipping_distances' => $tenant->catalogSetting?->shipping_distances ?? [],
                            'store_location_label' => $tenant->catalogSetting?->store_location_label,
                            // Only expose coordinates if distance mode is set
                            'store_latitude'     => $tenant->catalogSetting?->shipping_mode === 'distance'
                                ? $tenant->catalogSetting?->store_latitude : null,
                            'store_longitude'    => $tenant->catalogSetting?->shipping_mode === 'distance'
                                ? $tenant->catalogSetting?->store_longitude : null,
                        ]
                    ),
                ],
                'products' => $products
            ]
        ]);
    }
}
