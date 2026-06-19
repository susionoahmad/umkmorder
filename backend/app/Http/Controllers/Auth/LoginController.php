<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email atau password salah.'
            ], 401);
        }

        $user = Auth::user();
        $user->load('tenant');

        // Check if the tenant is active
        if (!$user->tenant || !$user->tenant->is_active) {
            Auth::logout();
            return response()->json([
                'status' => 'error',
                'message' => 'Akun bisnis Anda dinonaktifkan. Silakan hubungi admin.'
            ], 403);
        }

        // Generate Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'user' => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role,
                ],
                'tenant' => [
                    'id'                => $user->tenant->id,
                    'name'              => $user->tenant->name,
                    'slug'              => $user->tenant->slug,
                    'subscription_plan' => $user->tenant->subscription_plan ?? 'free',
                ]
            ]
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        // Gracefully handle expired/missing tokens — always return success
        $user = $request->user();
        if ($user) {
            try {
                $user->currentAccessToken()->delete();
            } catch (\Throwable) {
                // Token already gone — that's fine
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Logout berhasil'
        ]);
    }
}
