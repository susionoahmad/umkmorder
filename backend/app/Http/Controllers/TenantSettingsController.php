<?php

namespace App\Http\Controllers;

use App\Models\TenantCatalogSetting;
use App\Services\LocalUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TenantSettingsController extends Controller
{
    public function __construct(private LocalUploadService $uploads) {}
    public function show(): JsonResponse
    {
        $tenant = request()->user()->tenant()->with('catalogSetting')->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'tenant' => $tenant,
                'catalog_setting' => $tenant->catalogSetting,
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;
        $setting = TenantCatalogSetting::firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'catalog_title'           => $tenant->name,
                'catalog_description'     => null,
                'catalog_enabled'         => true,
                'auto_whatsapp_redirect'  => false,
                'show_price'              => true,
                'theme'                   => 'default',
            ]
        );

        $validator = Validator::make($request->all(), [
            'name'                    => 'required|string|max:150',
            'slug'                    => [
                'required',
                'string',
                'max:150',
                'alpha_dash',
                Rule::unique('tenants', 'slug')->ignore($tenant->id),
            ],
            'phone'                   => 'required|string|max:20',
            'address'                 => 'nullable|string',
            'logo'                    => ['nullable', 'string', 'max:500', function ($attribute, $value, $fail) {
                if (!$this->uploads->isLocalUpload($value)) {
                    $fail('Logo harus diunggah dari perangkat Anda, bukan URL eksternal.');
                }
            }],
            'catalog_title'           => 'required|string|max:150',
            'catalog_description'     => 'nullable|string',
            'catalog_banner'          => ['nullable', 'string', 'max:500', function ($attribute, $value, $fail) {
                if (!$this->uploads->isLocalUpload($value)) {
                    $fail('Banner harus diunggah dari perangkat Anda, bukan URL eksternal.');
                }
            }],
            'catalog_enabled'         => 'boolean',
            'auto_whatsapp_redirect'  => 'boolean',
            'show_price'              => 'boolean',
            'theme'                   => 'required|in:default,red,emerald,light,warm,ocean',
            'bank_transfer_info'      => 'nullable|string|max:1000',
            'qris_info'               => 'nullable|string|max:1000',
            'qris_image_url'          => ['nullable', 'string', 'max:500', function ($attribute, $value, $fail) {
                if (!$this->uploads->isLocalUpload($value)) {
                    $fail('QRIS harus diunggah dari perangkat Anda, bukan URL eksternal.');
                }
            }],
            // Shipping fields
            'shipping_mode'           => 'sometimes|in:none,zone,distance,api',
            'store_latitude'          => 'nullable|numeric|between:-90,90',
            'store_longitude'         => 'nullable|numeric|between:-180,180',
            'store_location_label'    => 'nullable|string|max:255',
            'shipping_zones'          => 'nullable|array',
            'shipping_zones.*.name'   => 'required_with:shipping_zones|string|max:100',
            'shipping_zones.*.cost'   => 'required_with:shipping_zones|numeric|min:0',
            'shipping_distances'          => 'nullable|array',
            'shipping_distances.*.max_km' => 'required_with:shipping_distances|numeric|min:0',
            'shipping_distances.*.cost'   => 'required_with:shipping_distances|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        if (empty($validated['logo']) && $tenant->logo) {
            $this->uploads->deleteIfLocal($tenant->logo);
        }

        if (empty($validated['catalog_banner']) && $setting->catalog_banner) {
            $this->uploads->deleteIfLocal($setting->catalog_banner);
        }

        if (empty($validated['qris_image_url']) && $setting->qris_image_url) {
            $this->uploads->deleteIfLocal($setting->qris_image_url);
        }

        $tenant->update(Arr::only($validated, [
            'name',
            'slug',
            'phone',
            'address',
            'logo',
        ]));

        $setting->update(Arr::only($validated, [
            'catalog_title',
            'catalog_description',
            'catalog_banner',
            'catalog_enabled',
            'auto_whatsapp_redirect',
            'show_price',
            'theme',
            'bank_transfer_info',
            'qris_info',
            'qris_image_url',
            'shipping_mode',
            'store_latitude',
            'store_longitude',
            'store_location_label',
            'shipping_zones',
            'shipping_distances',
        ]));

        $tenant->load('catalogSetting');

        return response()->json([
            'status'  => 'success',
            'message' => 'Pengaturan berhasil diperbarui',
            'data'    => [
                'tenant'          => $tenant,
                'catalog_setting' => $tenant->catalogSetting,
            ],
        ]);
    }

    /**
     * Generate a QR Code for the tenant's catalog URL.
     * Returns a base64-encoded PNG image (or SVG fallback).
     */
    public function getQrCode(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;
        $catalogUrl = "https://umkmorder.id/{$tenant->slug}";

        try {
            // Try PNG via GD first
            $qrImage = base64_encode(
                QrCode::format('png')->size(300)->margin(2)->generate($catalogUrl)
            );

            return response()->json([
                'status'   => 'success',
                'format'   => 'png',
                'qr_image' => 'data:image/png;base64,' . $qrImage,
                'url'      => $catalogUrl,
            ]);
        } catch (\Exception $e) {
            // Fallback to SVG (no GD dependency)
            try {
                $svgContent = QrCode::format('svg')->size(300)->margin(2)->generate($catalogUrl);
                $qrImage = base64_encode($svgContent);

                return response()->json([
                    'status'   => 'success',
                    'format'   => 'svg',
                    'qr_image' => 'data:image/svg+xml;base64,' . $qrImage,
                    'url'      => $catalogUrl,
                ]);
            } catch (\Exception $e2) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal membuat QR Code: ' . $e2->getMessage(),
                ], 500);
            }
        }
    }
}
