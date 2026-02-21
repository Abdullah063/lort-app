<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key');

        if (!$apiKey || $apiKey !== config('services.recommendation.api_key')) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz API anahtarı.',
            ], 401);
        }

        return $next($request);
    }
}