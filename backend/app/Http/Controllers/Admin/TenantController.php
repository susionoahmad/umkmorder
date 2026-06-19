<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TenantController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenants = Tenant::withCount(['products', 'orders', 'receivables'])
            ->orderByDesc('id')
            ->get();

        // Get owner details for listing
        foreach ($tenants as $t) {
            $owner = User::where('tenant_id', $t->id)->where('role', 'owner')->first();
            $t->owner_name = $owner?->name ?? '-';
            $t->owner_email = $owner?->email ?? '-';
        }

        return response()->json([
            'status' => 'success',
            'data' => $tenants,
        ]);
    }

    public function show(Tenant $tenant): JsonResponse
    {
        $tenant->loadCount(['products', 'orders', 'receivables']);
        
        $owner = User::where('tenant_id', $tenant->id)->where('role', 'owner')->first();
        $tenant->owner_name = $owner?->name ?? '-';
        $tenant->owner_email = $owner?->email ?? '-';

        return response()->json([
            'status' => 'success',
            'data' => $tenant,
        ]);
    }

    public function update(Request $request, Tenant $tenant): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'slug' => 'required|string|max:150|unique:tenants,slug,' . $tenant->id,
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'subscription_plan' => 'required|string|in:free,pro,business',
            'subscription_status' => 'required|string|in:trial,active,expired,suspended',
            'trial_ends_at' => 'nullable|date',
            'is_active' => 'required|boolean',
        ]);

        $tenant->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Detail toko berhasil diperbarui.',
            'data' => $tenant,
        ]);
    }

    public function suspend(Tenant $tenant): JsonResponse
    {
        $tenant->update([
            'is_active' => false,
            'subscription_status' => 'suspended',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Toko '{$tenant->name}' berhasil ditangguhkan.",
            'data' => $tenant,
        ]);
    }

    public function activate(Tenant $tenant): JsonResponse
    {
        $tenant->update([
            'is_active' => true,
            'subscription_status' => 'active',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Toko '{$tenant->name}' berhasil diaktifkan.",
            'data' => $tenant,
        ]);
    }

    public function resetPassword(Request $request, Tenant $tenant): JsonResponse
    {
        $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $owner = User::where('tenant_id', $tenant->id)->where('role', 'owner')->first();

        if (!$owner) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pemilik toko tidak ditemukan.',
            ], 404);
        }

        $owner->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Kata sandi untuk pemilik toko '{$tenant->name}' berhasil diatur ulang.",
        ]);
    }

    public function impersonate(Tenant $tenant): JsonResponse
    {
        $owner = User::where('tenant_id', $tenant->id)->where('role', 'owner')->first();

        if (!$owner) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pemilik toko tidak ditemukan untuk impersonasi.',
            ], 404);
        }

        // Generate impersonation token
        $token = $owner->createToken('impersonate_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => "Berhasil login sebagai pemilik toko '{$tenant->name}'.",
            'data' => [
                'token' => $token,
                'user' => $owner,
                'tenant' => $tenant->load('catalogSetting'),
            ]
        ]);
    }
}
