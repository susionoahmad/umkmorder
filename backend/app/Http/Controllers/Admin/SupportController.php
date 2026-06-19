<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function tenants(): JsonResponse
    {
        $tenants = Tenant::orderBy('name')->get();

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

    public function updateNotes(Request $request, Tenant $tenant): JsonResponse
    {
        $request->validate([
            'support_notes' => 'nullable|string',
        ]);

        $tenant->update([
            'support_notes' => $request->support_notes,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Catatan internal support berhasil disimpan.',
            'data' => $tenant,
        ]);
    }
}
