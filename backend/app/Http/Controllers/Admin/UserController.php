<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::with('tenant')->orderByDesc('id')->get();
        return response()->json([
            'status' => 'success',
            'data' => $users,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:super_admin,owner,staff',
            'tenant_id' => 'nullable|required_if:role,owner,staff|exists:tenants,id',
        ]);

        $userData = $request->only(['name', 'email', 'role', 'tenant_id']);
        if ($request->role === 'super_admin') {
            $userData['tenant_id'] = null;
        }
        $userData['password'] = Hash::make($request->password);

        $user = User::create($userData);

        return response()->json([
            'status' => 'success',
            'message' => 'Pengguna baru berhasil dibuat.',
            'data' => $user->load('tenant'),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $user->load('tenant'),
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => 'required|string|in:super_admin,owner,staff',
            'tenant_id' => 'nullable|required_if:role,owner,staff|exists:tenants,id',
        ]);

        $userData = $request->only(['name', 'email', 'role', 'tenant_id']);
        
        // Proteksi agar user tidak merubah role-nya sendiri dari super_admin jika dia adalah super admin yang sedang login
        if ($user->id === auth()->id() && $request->role !== 'super_admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak dapat mengubah hak akses super admin Anda sendiri.',
            ], 400);
        }

        if ($request->role === 'super_admin') {
            $userData['tenant_id'] = null;
        }

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return response()->json([
            'status' => 'success',
            'message' => 'Detail pengguna berhasil diperbarui.',
            'data' => $user->load('tenant'),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak dapat menghapus akun Anda sendiri.',
            ], 400);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Pengguna berhasil dihapus.',
        ]);
    }
}
