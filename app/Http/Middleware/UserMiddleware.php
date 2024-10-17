<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, ['user', 'admin'])) {
            return response()->json([
                'status' => 403,
                'message' => !$user ? 'User not found or not authenticated' : 'Unauthorized Access: User or Admin only',
            ], 403);
        }

        return $next($request);
    }

}
