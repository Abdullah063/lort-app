<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SupportedLanguage;

class SetLocaleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Header'dan dil kodunu al
        $lang = $request->header('Accept-Language', 'tr');

        // Sadece ilk 2 karakteri al (en-US → en)
        $lang = substr($lang, 0, 2);

        // 2. Desteklenen dil mi kontrol et
        $supported = SupportedLanguage::where('code', $lang)
            ->where('is_active', true)
            ->exists();

        if (!$supported) {
            // Varsayılan dili bul
            $default = SupportedLanguage::where('is_default', true)->first();
            $lang = $default ? $default->code : 'tr';
        }

        // 3. Uygulama dilini ayarla
        app()->setLocale($lang);

        // 4. Request'e dil bilgisini ekle (controller'larda kullanmak için)
        $request->merge(['_locale' => $lang]);

        return $next($request);
    }
}