<?php

namespace App\Http\Controllers;

use App\Models\TenantCatalogSetting;
use App\Services\LocalUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UploadController extends Controller
{
    public function __construct(private LocalUploadService $uploads) {}

    /**
     * Upload logo image for the authenticated tenant.
     * Stores file in public/uploads/logos/
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,jpg,png,webp,gif|max:2048',
        ]);

        $tenant = $request->user()->tenant;
        $file   = $request->file('file');

        $this->uploads->deleteIfLocal($tenant->logo);

        $url = $this->uploads->storeImage($file, 'logos', 'logo', $tenant->id);

        $tenant->update(['logo' => $url]);

        return response()->json([
            'status' => 'success',
            'url'    => $url,
        ]);
    }

    /**
     * Upload banner image for the authenticated tenant.
     * Stores file in public/uploads/banners/
     */
    public function uploadBanner(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,jpg,png,webp,gif|max:5120',
        ]);

        $tenant  = $request->user()->tenant;
        $setting = TenantCatalogSetting::firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'catalog_title'          => $tenant->name,
                'catalog_description'    => null,
                'catalog_enabled'        => true,
                'auto_whatsapp_redirect' => false,
                'show_price'             => true,
                'theme'                  => 'default',
            ]
        );
        $file = $request->file('file');

        $this->uploads->deleteIfLocal($setting->catalog_banner);

        $url = $this->uploads->storeImage($file, 'banners', 'banner', $tenant->id);

        $setting->update(['catalog_banner' => $url]);

        return response()->json([
            'status' => 'success',
            'url'    => $url,
        ]);
    }

    /**
     * Upload product image for the authenticated tenant.
     * Stores file in public/uploads/products/
     */
    public function uploadProductImage(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,jpg,png,webp,gif|max:3072',
        ]);

        $tenant = $request->user()->tenant;
        $file   = $request->file('file');

        $url = $this->uploads->storeImage($file, 'products', 'product', $tenant->id);

        return response()->json([
            'status' => 'success',
            'url'    => $url,
        ]);
    }

    /**
     * Upload static QRIS image for the authenticated tenant.
     * Stores file in public/uploads/qris/
     */
    public function uploadQris(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,jpg,png,webp|max:3072',
        ]);

        $tenant  = $request->user()->tenant;
        $setting = TenantCatalogSetting::firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'catalog_title'          => $tenant->name,
                'catalog_description'    => null,
                'catalog_enabled'        => true,
                'auto_whatsapp_redirect' => false,
                'show_price'             => true,
                'theme'                  => 'default',
            ]
        );
        $file = $request->file('file');

        $this->uploads->deleteIfLocal($setting->qris_image_url);

        $url = $this->uploads->storeImage($file, 'qris', 'qris', $tenant->id);

        $setting->update([
            'qris_image_url' => $url,
            'qris_info'      => $setting->qris_info ?: $url,
        ]);

        return response()->json([
            'status' => 'success',
            'url'    => $url,
        ]);
    }
}
