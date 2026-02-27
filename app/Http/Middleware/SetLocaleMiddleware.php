<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\SupportedLanguage;

class SetLocaleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $lang = substr($request->header('Accept-Language', 'en'), 0, 2);

        $supportedCodes = Cache::remember('supported_languages', 86400, function () {
            return SupportedLanguage::where('is_active', true)->pluck('code')->toArray();
        });

        if (!in_array($lang, $supportedCodes)) {
            $lang = Cache::remember('default_language', 86400, function () {
                return SupportedLanguage::where('is_default', true)->first()?->code ?? 'en';
            });
        }

        app()->setLocale($lang);
        $request->merge(['_locale' => $lang]);

        return $next($request);
    }
}