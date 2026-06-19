<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
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
        ]);

        $settings->fill($request->all());
        $settings->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Pengaturan platform berhasil diperbarui.',
            'data' => $settings,
        ]);
    }
}
