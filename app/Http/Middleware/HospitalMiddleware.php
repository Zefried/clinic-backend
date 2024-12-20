<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HospitalMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if the user is authenticated and has either 'user' or 'admin' role
        // User routes can be accessed through admin as well
        
        if ($user && ($user->role === 'hospital' || $user->role === 'admin')) {
            return $next($request);
        } else {
            return response()->json([
                'status' => 403,
                'message' => !$user ? 'User not found or not authenticated' : 'Unauthorized Access: User only',
            ], 403);
        }
    
    }
}
