<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Tenant;

class RegisterController extends Controller
{
    /**
     * Cek ketersediaan slug toko.
     */
    public function checkSlug(Request $request)
    {
        $request->validate(['slug' => 'required|string|min:3|max:50']);

        $slug = Str::slug($request->slug);
        $exists = Tenant::where('slug', $slug)->exists();

        return response()->json([
            'status'    => 'success',
            'slug'      => $slug,
            'available' => !$exists,
        ]);
    }

    /**
     * Daftarkan tenant baru beserta user owner-nya.
     * Semua dieksekusi dalam satu DB transaction.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:150',
            'email'                 => 'required|email|unique:users,email|max:255',
            'phone'                 => 'required|string|max:20',
            'password'              => 'required|string|min:8|confirmed',
            'tenant_name'           => 'required|string|max:150',
            'slug'                  => 'required|string|min:3|max:150|unique:tenants,slug|regex:/^[a-z0-9\-]+$/',
            'address'               => 'nullable|string|max:500',
            'subscription_plan'     => 'nullable|in:free,pro',
        ], [
            'email.unique'          => 'Email sudah terdaftar, gunakan email lain atau login.',
            'slug.unique'           => 'Link toko sudah digunakan, coba nama lain.',
            'slug.regex'            => 'Slug hanya boleh huruf kecil, angka, dan strip (-).',
            'password.confirmed'    => 'Konfirmasi password tidak cocok.',
            'password.min'          => 'Password minimal 8 karakter.',
        ]);

        try {
            $result = DB::transaction(function () use ($validated) {
                // 1. Buat tenant
                $tenant = Tenant::create([
                    'name'              => $validated['tenant_name'],
                    'slug'              => $validated['slug'],
                    'phone'             => $validated['phone'],
                    'address'           => $validated['address'] ?? null,
                    'subscription_plan' => $validated['subscription_plan'] ?? 'free',
                    'is_active'         => true,
                ]);

                // 2. Buat default catalog settings
                $tenant->catalogSetting()->create([
                    'catalog_title'           => $validated['tenant_name'],
                    'catalog_description'     => null,
                    'catalog_enabled'         => true,
                    'auto_whatsapp_redirect'  => false,
                    'show_price'              => true,
                    'theme'                   => 'default',
                ]);

                // 3. Buat user owner yang terhubung ke tenant
                $user = User::create([
                    'tenant_id' => $tenant->id,
                    'name'      => $validated['name'],
                    'email'     => $validated['email'],
                    'password'  => Hash::make($validated['password']),
                    'role'      => 'owner',
                ]);

                // 4. Buat token Sanctum
                $token = $user->createToken('auth_token')->plainTextToken;

                $tenant->load('catalogSetting');
                return compact('token', 'user', 'tenant');
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Toko berhasil dibuat! Selamat bergabung.',
                'data'    => [
                    'token'  => $result['token'],
                    'user'   => $result['user'],
                    'tenant' => $result['tenant'],
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pendaftaran gagal. Silakan coba lagi.',
                'detail'  => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
