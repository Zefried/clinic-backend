<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        
         $user = $request->user();

         if (!$user || $user->role !== 'admin') {
             return response()->json([
                 'status' => 403,
                 'message' => !$user ? 'User not found or not authenticated' : 'Unauthorized Access: Admins only',
             ], 403);
         }
 
         return $next($request);
 
        // return response()->json($request->header());
    }
}
