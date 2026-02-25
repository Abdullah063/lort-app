<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RecommendationService
{
     private static string $baseUrl = 'https://openrouter.ai/api/v1/chat/completions';
    private static function getApiKey(): string
    {
        $keys = config('services.gemini.api_keys');

        if (empty($keys)) {
            return config('services.gemini.api_key');
        }

        $index = (int) \Illuminate\Support\Facades\Cache::increment('gemini_key_index');
        return $keys[$index % count($keys)];
    }

    public static function generate(array $profile, ?string $lang = null): ?string
    {
        $lang = $lang ?? app()->getLocale();
        $prompt = self::buildPrompt($profile, $lang);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openrouter.api_key'),
        ])->post(self::$baseUrl, [
            'model' => 'openrouter/free',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.8,
            'max_tokens' => 2048,
        ]);

        if (!$response->successful()) {
            \Log::error('OpenRouter API error', [
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);
            return null;
        }

        return $response->json('choices.0.message.content');
    }

    public static function getForUser(int $userId, array $profile, ?string $lang = null): ?string
    {
        $lang = $lang ?? app()->getLocale();
        $cacheKey = "recommendation:user:{$userId}:{$lang}";

        return Cache::remember($cacheKey, 60 * 60 * 24, function () use ($profile, $lang) {
            return self::generate($profile, $lang);
        });
    }

    public static function clearCache(int $userId): void
    {
        Cache::forget("recommendation:user:{$userId}");
    }

    /**
     * Web sitesinin içeriğini detaylı çek ve temizle
     */
    private static function fetchWebsiteContent(string $url): string
    {
        // URL'ye protokol ekle
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'https://' . $url;
        }

        try {
            $html = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; LortApp/1.0)'])
                ->get($url)
                ->body();

            $result = '';

            // Title çek
            if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
                $result .= 'Site Başlığı: ' . trim(html_entity_decode($m[1])) . "\n";
            }

            // Meta description çek
            if (preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\'](.*?)["\']/is', $html, $m)) {
                $result .= 'Açıklama: ' . trim(html_entity_decode($m[1])) . "\n";
            }

            // Meta keywords çek
            if (preg_match('/<meta[^>]*name=["\']keywords["\'][^>]*content=["\'](.*?)["\']/is', $html, $m)) {
                $result .= 'Anahtar Kelimeler: ' . trim(html_entity_decode($m[1])) . "\n";
            }

            // H1, H2, H3 başlıkları çek
            $headings = [];
            if (preg_match_all('/<h[1-3][^>]*>(.*?)<\/h[1-3]>/is', $html, $matches)) {
                foreach ($matches[1] as $h) {
                    $clean = trim(strip_tags($h));
                    if (!empty($clean) && mb_strlen($clean) > 2) {
                        $headings[] = $clean;
                    }
                }
            }
            if (!empty($headings)) {
                $result .= 'Öne Çıkan Başlıklar: ' . implode(' | ', array_slice($headings, 0, 10)) . "\n";
            }

            // Ana içerik - script, style, nav, footer, header kaldır
            $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
            $content = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $content);
            $content = preg_replace('/<nav\b[^>]*>(.*?)<\/nav>/is', '', $content);
            $content = preg_replace('/<footer\b[^>]*>(.*?)<\/footer>/is', '', $content);
            $content = preg_replace('/<header\b[^>]*>(.*?)<\/header>/is', '', $content);
            $content = strip_tags($content);
            $content = preg_replace('/\s+/', ' ', $content);
            $content = trim($content);

            if (!empty($content)) {
                $result .= 'Site İçeriği: ' . mb_substr($content, 0, 3000);
            }

            return $result;
        } catch (\Exception $e) {
            \Log::warning('Website fetch failed: ' . $url, ['error' => $e->getMessage()]);
            return '';
        }
    }

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

        $website = $profile['website'] ?? '';
        $langName = self::getLanguageName($lang);

        $websiteSection = '';
        $websiteInstruction = '';

        if (!empty($website)) {
            $websiteContent = self::fetchWebsiteContent($website);

            $websiteSection = "\n- Web Sitesi: {$website}";

            if (!empty($websiteContent)) {
                $websiteSection .= "\n- Web Sitesi İçeriği: {$websiteContent}";
                $websiteInstruction = "0. WEB SİTESİ ANALİZİ: Kullanıcının web sitesini ({$website}) analiz ettin. Site içeriğine dayanarak şirketin ne yaptığını özetle ve networking önerilerini buna göre şekillendir. Siteyle ilgili kısa bir pozitif yorum yap.\n";
            } else {
                $websiteInstruction = "0. WEB SİTESİ YORUMU: Kullanıcının web sitesi ({$website}) var. URL'den ve diğer bilgilerden yola çıkarak kısa bir yorum yap.\n";
            }
        }

        return <<<PROMPT
Sen Lord App'in akıllı iş asistanısın. Lord App, girişimcileri ve iş insanlarını Tinder tarzı swipe ile eşleştiren bir iş networking platformu.

Kullanıcının profiline bakarak ONA ÖZEL, SOMUT ve AKSİYON ALINABİLİR öneriler yaz. Genel laflar yazma.

## Kullanıcı Profili:
- İsim pozisyonu: {$position}
- Şirket: {$company}
- Sektör: {$sector}
- Şehir: {$city}
- Hedefler: {$goals}
- İlgi Alanları: {$interests}{$websiteSection}

## Mesajda şunları yap:
{$websiteInstruction}1. BAĞLANTI ÖNERİSİ: Hedeflerine göre platformda hangi tür kişileri swipe etmesi gerektiğini söyle (örn: "İhracatçı arıyorsan, gıda sektöründeki profillere göz at")
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