<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(private \App\Services\LocalUploadService $uploads) {}

    public function show(): JsonResponse
    {
        $settings = PlatformSetting::first();
        
        if (!$settings) {
            $settings = PlatformSetting::create([
                'app_name' => 'UMKMOrder',
                'company_name' => 'UMKMOrder Corp',
                'support_email' => 'support@umkmorder.id',
                'support_whatsapp' => '6281234567890',
                'logo_url' => null,
                'favicon_url' => null,
                'default_trial_duration' => 30,
                'maintenance_mode' => false,
                'admin_bank_transfer_info' => "772-0988-123\nBank BCA — PT UMKM Order Indonesia",
                'admin_qris_image_url' => null,
                'admin_qris_info' => 'QRIS GPN UMKM-ORDER',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $settings,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $settings = PlatformSetting::first();
        
        if (!$settings) {
            $settings = new PlatformSetting();
        }

        $request->validate([
            'app_name' => 'required|string|max:100',
            'company_name' => 'required|string|max:150',
            'support_email' => 'required|email|max:100',
            'support_whatsapp' => 'required|string|max:20',
            'default_trial_duration' => 'required|integer|min:0',
            'maintenance_mode' => 'required|boolean',
            'admin_bank_transfer_info' => 'nullable|string|max:1000',
            'admin_qris_image_url' => 'nullable|string|max:500',
            'admin_qris_info' => 'nullable|string|max:255',
        ]);

        $oldQrisImageUrl = $settings->admin_qris_image_url;

        $settings->fill($request->all());

        if (empty($request->admin_qris_image_url) && $oldQrisImageUrl) {
            $this->uploads->deleteIfLocal($oldQrisImageUrl);
        }

        $settings->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Pengaturan platform berhasil diperbarui.',
            'data' => $settings,
        ]);
    }

    public function uploadQris(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,jpg,png,webp|max:3072',
        ]);

        $settings = PlatformSetting::first();
        if (!$settings) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pengaturan platform tidak ditemukan.',
            ], 404);
        }

        $file = $request->file('file');

        if ($settings->admin_qris_image_url) {
            $this->uploads->deleteIfLocal($settings->admin_qris_image_url);
        }

        $url = $this->uploads->storeImage($file, 'qris', 'admin_qris', 0);

        $settings->update([
            'admin_qris_image_url' => $url,
        ]);

        return response()->json([
            'status' => 'success',
            'url'    => $url,
        ]);
    }
}
