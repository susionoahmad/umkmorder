<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPriceTier;
use App\Services\LocalUploadService;
use App\Services\PricingService;
use App\Services\SubscriptionPlanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function __construct(
        private LocalUploadService $uploads,
        private PricingService $pricingService,
        private SubscriptionPlanService $subscriptionPlans,
    ) {}

    public function index(): JsonResponse
    {
        $products = Product::with('priceTiers')->get();
        return response()->json([
            'status' => 'success',
            'data'   => $products
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'                      => 'required|string|max:150',
            'sku'                       => 'nullable|string|max:50',
            'price'                     => 'required|numeric|min:0',
            'stock'                     => 'nullable|integer|min:0',
            'unit'                      => 'nullable|string|max:50',
            'description'               => 'nullable|string',
            'image_url'                 => 'nullable|url|max:2048',
            'show_image'                => 'boolean',
            'is_active'                 => 'boolean',
            // Tier pricing (optional)
            'price_tiers'               => 'nullable|array',
            'price_tiers.*.min_qty'     => 'required|integer|min:1',
            'price_tiers.*.max_qty'     => 'nullable|integer|min:1',
            'price_tiers.*.unit_price'  => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $tiersData = $validated['price_tiers'] ?? [];
        $willBeActive = $validated['is_active'] ?? true;

        if ($willBeActive && !$this->subscriptionPlans->canActivateProduct($request->user()->tenant)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Batas 20 produk aktif untuk Paket Gratis sudah tercapai. Arsipkan produk lain atau upgrade ke Paket Pro untuk produk aktif tak terbatas.',
            ], 422);
        }

        // Validate tier overlaps
        if (!empty($tiersData)) {
            $overlapError = $this->pricingService->validateTiers($tiersData);
            if ($overlapError) {
                return response()->json(['status' => 'error', 'message' => $overlapError], 422);
            }
        }

        return DB::transaction(function () use ($validated, $tiersData) {
            $product = Product::create(collect($validated)->except('price_tiers')->toArray());

            $this->syncTiers($product, $tiersData);

            return response()->json([
                'status'  => 'success',
                'message' => 'Produk berhasil dibuat',
                'data'    => $product->load('priceTiers'),
            ], 201);
        });
    }

    public function show($id): JsonResponse
    {
        $product = Product::with('priceTiers')->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data'   => $product
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'                      => 'required|string|max:150',
            'sku'                       => 'nullable|string|max:50',
            'price'                     => 'required|numeric|min:0',
            'stock'                     => 'nullable|integer|min:0',
            'unit'                      => 'nullable|string|max:50',
            'description'               => 'nullable|string',
            'image_url'                 => 'nullable|url|max:2048',
            'show_image'                => 'boolean',
            'is_active'                 => 'boolean',
            // Tier pricing (optional)
            'price_tiers'               => 'nullable|array',
            'price_tiers.*.min_qty'     => 'required|integer|min:1',
            'price_tiers.*.max_qty'     => 'nullable|integer|min:1',
            'price_tiers.*.unit_price'  => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated  = $validator->validated();
        $tiersData  = $validated['price_tiers'] ?? [];
        $willBeActive = $validated['is_active'] ?? $product->is_active;

        if ($willBeActive && !$this->subscriptionPlans->canActivateProduct($request->user()->tenant, $product->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Batas 20 produk aktif untuk Paket Gratis sudah tercapai. Arsipkan produk lain atau upgrade ke Paket Pro untuk produk aktif tak terbatas.',
            ], 422);
        }

        // Validate tier overlaps
        if (!empty($tiersData)) {
            $overlapError = $this->pricingService->validateTiers($tiersData);
            if ($overlapError) {
                return response()->json(['status' => 'error', 'message' => $overlapError], 422);
            }
        }

        return DB::transaction(function () use ($product, $validated, $tiersData) {
            $oldImageUrl = $product->image_url;
            $product->update(collect($validated)->except('price_tiers')->toArray());

            // Cleanup old local image if changed
            if (($validated['image_url'] ?? null) !== $oldImageUrl) {
                $this->uploads->deleteIfLocal($oldImageUrl);
            }

            $this->syncTiers($product, $tiersData);

            return response()->json([
                'status'  => 'success',
                'message' => 'Produk berhasil diperbarui',
                'data'    => $product->load('priceTiers'),
            ]);
        });
    }

    public function destroy($id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $this->uploads->deleteIfLocal($product->image_url);
        $product->delete(); // Cascade deletes tiers via FK

        return response()->json([
            'status'  => 'success',
            'message' => 'Produk berhasil dihapus'
        ]);
    }

    /**
     * Replace all tiers for a product with the provided array.
     * Passing an empty array removes all tiers.
     */
    private function syncTiers(Product $product, array $tiersData): void
    {
        // Remove existing tiers
        ProductPriceTier::withoutGlobalScopes()
            ->where('product_id', $product->id)
            ->delete();

        if (empty($tiersData)) {
            return;
        }

        $tenantId = $product->tenant_id;
        $rows = [];
        $now  = now();

        foreach ($tiersData as $tier) {
            $maxQty = isset($tier['max_qty']) && $tier['max_qty'] !== '' && $tier['max_qty'] !== null
                ? (int) $tier['max_qty']
                : null;

            $rows[] = [
                'tenant_id'  => $tenantId,
                'product_id' => $product->id,
                'min_qty'    => (int) $tier['min_qty'],
                'max_qty'    => $maxQty,
                'unit_price' => (float) $tier['unit_price'],
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        ProductPriceTier::insert($rows);
    }
}
