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

        if (!auth('api')->check()) {
            return response()->json([
                'message' => 'Erişim izni yok'
            ], 401);
        }
        $user = auth('api')->user();



        if (!$user->isSuperAdmin()) {
            return response()->json([
                'message' => 'Bu işlem için super admin yetkisi gerekli'
            ], 403);
        }
        return $next($request);
    }
}
