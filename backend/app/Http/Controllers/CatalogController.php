<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * Mengambil informasi toko/tenant
     */
    public function getTenantInfo($slug)
    {
        // Kita bisa asumsikan tenant pasti ada dan aktif karena sudah melewati Middleware
        $tenant = Tenant::with('catalogSetting')->where('slug', $slug)->first();

        return response()->json([
            'status' => 'success',
            'data' => $tenant
        ]);
    }

    /**
     * Mengambil produk dari toko tertentu
     */
    public function getProducts($slug)
    {
        // 1. Cari ID tenant berdasarkan slug
        $tenant = Tenant::where('slug', $slug)->first();

        // 2. Ambil produk milik tenant tersebut. 
        // Pastikan hanya mengambil produk yang is_active = true (bukan draft)
        // Catatan: Di katalog publik, Global Scope `BelongsToTenant` TIDAK JALAN 
        // karena user belum login. Oleh karena itu kita wajib set where('tenant_id', ...) secara eksplisit di sini.

        $products = Product::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'toko' => $tenant->name,
                'products' => $products
            ]
        ]);
    }
}