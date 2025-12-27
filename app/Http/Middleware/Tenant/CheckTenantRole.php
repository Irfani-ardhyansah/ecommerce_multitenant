<?php

namespace App\Http\Middleware\Tenant;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckTenantRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. Cek apakah user sudah login?
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // 2. Ambil user yang sedang login (User Tenant)
        $user = Auth::user();

        // 3. Cek apakah role-nya sesuai dengan yang diminta?
        // Kita bandingkan role di database dengan parameter $role
        if ($user->role !== $role) {
            return response()->json(['message' => 'Unauthorized. Access denied.'], 403);
        }

        return $next($request);
    }
}