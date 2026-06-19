<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifySuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->isSuperAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Rute ini memerlukan hak akses Super Admin.',
            ], 403);
        }

        return $next($request);
    }
}
