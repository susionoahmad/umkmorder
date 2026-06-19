<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;

class VerifyActiveTenant
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // ------------------------------------------------------------
        // KONDISI 1: JIKA USER SUDAH LOGIN (Dashboard Tenant)
        // ------------------------------------------------------------
        if ($user) {
            $tenant = $user->tenant;

            // Antisipasi jika user tidak terikat ke tenant mana pun
            if (!$tenant) {
                return $this->errorResponse($request, 'Akun Anda tidak terhubung dengan toko mana pun.', 403);
            }

            // Verifikasi apakah tenant masih aktif
            if (!$tenant->is_active) {
                // Hapus token akses Sanctum agar user otomatis log out
                if ($user->currentAccessToken()) {
                    $user->currentAccessToken()->delete();
                }

                return $this->errorResponse($request, 'Akun toko Anda sedang dinonaktifkan. Silakan hubungi admin.', 403);
            }

            // KUNCI 1: Ikat tenant_id ke request untuk kebutuhan internal (opsional tapi aman)
            $request->merge(['current_tenant_id' => $tenant->id]);

            return $next($request);
        }

        // ------------------------------------------------------------
        // KONDISI 2: JIKA PENGUNJUNG TANPA LOGIN (Katalog Publik via Slug)
        // ------------------------------------------------------------
        $slug = $request->route('slug');
        if ($slug) {
            $tenant = Tenant::where('slug', $slug)->first();

            // Tolak akses jika toko tidak terdaftar atau tidak aktif
            if (!$tenant || !$tenant->is_active) {
                return $this->errorResponse($request, 'Toko tidak ditemukan atau sedang tidak aktif.', 404);
            }

            // KUNCI 2: Suntikkan tenant_id ke dalam request agar bisa dibaca oleh Trait BelongsToTenant!
            $request->merge(['current_tenant_id' => $tenant->id]);
        }

        return $next($request);
    }

    /**
     * Helper untuk standarisasi format error JSON / Web Abort
     */
    private function errorResponse(Request $request, string $message, int $statusCode)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], $statusCode);
        }

        abort($statusCode, $message);
    }
}