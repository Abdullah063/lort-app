<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RecommendationService
{
    private static string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent';

    /**
     * Kullanıcı bilgilerine göre öneri metni üret
     */
    public static function generate(array $profile, ?string $lang = null): ?string
    {
        $lang = $lang ?? app()->getLocale();
        $prompt = self::buildPrompt($profile, $lang);

        $response = Http::post(self::$baseUrl . '?key=' . config('services.gemini.api_key'), [
            'contents' => [
                [
                    'parts' => [['text' => $prompt]]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.8,
                'maxOutputTokens' => 2048,
            ],
        ]);

        if (!$response->successful()) {
            \Log::error('Gemini API error', [
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);
            return null;
        }

        return $response->json('candidates.0.content.parts.0.text');
    }

    /**
     * Öneriyi cache'li döndür (aynı profil için tekrar API'ye gitmesin)
     * Cache süresi: 24 saat, dil bazlı cache
     */
    public static function getForUser(int $userId, array $profile, ?string $lang = null): ?string
    {
        $lang = $lang ?? app()->getLocale();
        $cacheKey = "recommendation:user:{$userId}:{$lang}";

        return Cache::remember($cacheKey, 60 * 60 * 24, function () use ($profile, $lang) {
            return self::generate($profile, $lang);
        });
    }

    /**
     * Kullanıcı cache'ini temizle (profil güncellenince çağır)
     */
    public static function clearCache(int $userId): void
    {
        Cache::forget("recommendation:user:{$userId}");
    }

    /**
     * Prompt oluştur
     */
    private static function buildPrompt(array $profile, string $lang): string
    {
        $sector = $profile['sector'] ?? '';
        $city = $profile['city'] ?? '';
        $company = $profile['company'] ?? '';
        $position = $profile['position'] ?? '';

        $goals = '';
        if (!empty($profile['goals'])) {
            $goals = is_array($profile['goals']) ? implode(', ', $profile['goals']) : $profile['goals'];
        }

        $interests = '';
        if (!empty($profile['interests'])) {
            $interests = is_array($profile['interests']) ? implode(', ', $profile['interests']) : $profile['interests'];
        }

        $langName = self::getLanguageName($lang);

        return <<<PROMPT
Sen Lord App'in akıllı iş asistanısın. Lord App, girişimcileri ve iş insanlarını Tinder tarzı swipe ile eşleştiren bir iş networking platformu.

Kullanıcının profiline bakarak ONA ÖZEL, SOMUT ve AKSİYON ALINABİLİR öneriler yaz. Genel laflar yazma.

## Kullanıcı Profili:
- İsim pozisyonu: {$position}
- Şirket: {$company}
- Sektör: {$sector}
- Şehir: {$city}
- Hedefler: {$goals}
- İlgi Alanları: {$interests}

## Mesajda şunları yap:
1. BAĞLANTI ÖNERİSİ: Hedeflerine göre platformda hangi tür kişileri swipe etmesi gerektiğini söyle (örn: "İhracatçı arıyorsan, gıda sektöründeki profillere göz at")
2. PROFİL İPUCU: Profilinde eksik veya geliştirebileceği 1 somut şey söyle (örn: "Şirket açıklamana ihracat deneyimini eklersen daha çok eşleşme alırsın")
3. HAFTALIK HEDEF: Bu hafta platformda yapabileceği 1 küçük aksiyon ver (örn: "Bu hafta 10 yeni profile bak ve en az 5'ini beğen")

## Ton ve Format:
- {$langName} dilinde yaz
- MUTLAKA sen dili kullan (senin, sana, yapabilirsin), asla siz deme
- Samimi ve arkadaşça ol, sanki iş arkadaşıyla konuşuyormuş gibi
- Kısa tut, toplamda 4-5 cümle yeterli
- Emoji kullanma
- Başlık, madde işareti, yıldız veya numara kullanma, düz paragraf yaz
- Kullanıcıdan bilgi isteme, elindeki bilgilerle yaz
PROMPT;
    }

    /**
     * Dil kodundan dil adı döndür
     */
    private static function getLanguageName(string $lang): string
    {
        return match ($lang) {
            'tr' => 'Türkçe',
            'en' => 'İngilizce (English)',
            'de' => 'Almanca (Deutsch)',
            'fr' => 'Fransızca (Français)',
            'ar' => 'Arapça (العربية)',
            'es' => 'İspanyolca (Español)',
            'ru' => 'Rusça (Русский)',
            'zh' => 'Çince (中文)',
            'ja' => 'Japonca (日本語)',
            'ko' => 'Korece (한국어)',
            default => 'İngilizce (English)',
        };
    }
}