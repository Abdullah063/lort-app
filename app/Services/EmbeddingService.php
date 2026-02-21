<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class EmbeddingService
{
    private static string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-embedding-001:embedContent';

    /**
     * Metni vektöre çevir
     * Sonucu cache'le (aynı metin için tekrar API'ye gitmesin)
     */
    public static function getEmbedding(string $text, ?string $cacheKey = null): ?array
    {
        // Cache varsa oradan dön
        if ($cacheKey) {
            $cached = Cache::get("embedding:{$cacheKey}");
            if ($cached) {
                return $cached;
            }
        }

        $response = Http::post(self::$baseUrl . '?key=' . config('services.gemini.api_key'), [
            'content' => [
                'parts' => [['text' => $text]]
            ]
        ]);

        if (!$response->successful()) {
            return null;
        }

        $embedding = $response->json('embedding.values');

        // Cache'e yaz (7 gün)
        if ($cacheKey && $embedding) {
            Cache::put("embedding:{$cacheKey}", $embedding, 60 * 60 * 24 * 7);
        }

        return $embedding;
    }

    /**
     * Kullanıcı profili için embedding oluştur
     */
    public static function forUser(int $userId, array $profile): ?array
    {
        $text = self::buildUserText($profile);
        return self::getEmbedding($text, "user:{$userId}");
    }

    /**
     * Şirket için embedding oluştur
     */
    public static function forCompany(int $companyId, array $data): ?array
    {
        $text = self::buildCompanyText($data);
        return self::getEmbedding($text, "company:{$companyId}");
    }

    /**
     * İş ilanı için embedding oluştur
     */
    public static function forJob(int $jobId, array $data): ?array
    {
        $text = self::buildJobText($data);
        return self::getEmbedding($text, "job:{$jobId}");
    }

    /**
     * İki vektör arasındaki benzerliği hesapla (Cosine Similarity)
     * Sonuç: 0.0 (hiç benzemez) → 1.0 (aynı)
     */
    public static function cosineSimilarity(array $a, array $b): float
    {
        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        $len = count($a);
        for ($i = 0; $i < $len; $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        $denominator = sqrt($normA) * sqrt($normB);

        return $denominator > 0 ? $dotProduct / $denominator : 0.0;
    }

    /**
     * Kullanıcı profilini tek metin haline getir
     */
    private static function buildUserText(array $profile): string
    {
        $parts = [];

        if (!empty($profile['sector'])) {
            $parts[] = "Sektör: {$profile['sector']}";
        }
        if (!empty($profile['city'])) {
            $parts[] = "Şehir: {$profile['city']}";
        }
        if (!empty($profile['skills'])) {
            $skills = is_array($profile['skills']) ? implode(', ', $profile['skills']) : $profile['skills'];
            $parts[] = "Beceriler: {$skills}";
        }
        if (!empty($profile['experience_years'])) {
            $parts[] = "Deneyim: {$profile['experience_years']} yıl";
        }
        if (!empty($profile['education_level'])) {
            $parts[] = "Eğitim: {$profile['education_level']}";
        }
        if (!empty($profile['bio'])) {
            $parts[] = $profile['bio'];
        }

        return implode('. ', $parts);
    }

    /**
     * Şirket bilgisini tek metin haline getir
     */
    private static function buildCompanyText(array $data): string
    {
        $parts = [];

        if (!empty($data['name'])) {
            $parts[] = $data['name'];
        }
        if (!empty($data['sector'])) {
            $parts[] = "Sektör: {$data['sector']}";
        }
        if (!empty($data['city'])) {
            $parts[] = "Şehir: {$data['city']}";
        }
        if (!empty($data['description'])) {
            $parts[] = $data['description'];
        }

        return implode('. ', $parts);
    }

    /**
     * İş ilanını tek metin haline getir
     */
    private static function buildJobText(array $data): string
    {
        $parts = [];

        if (!empty($data['title'])) {
            $parts[] = $data['title'];
        }
        if (!empty($data['sector'])) {
            $parts[] = "Sektör: {$data['sector']}";
        }
        if (!empty($data['city'])) {
            $parts[] = "Şehir: {$data['city']}";
        }
        if (!empty($data['required_skills'])) {
            $skills = is_array($data['required_skills']) ? implode(', ', $data['required_skills']) : $data['required_skills'];
            $parts[] = "Aranan beceriler: {$skills}";
        }
        if (!empty($data['description'])) {
            $parts[] = $data['description'];
        }

        return implode('. ', $parts);
    }

    /**
     * Kullanıcının embedding cache'ini temizle (profil güncellenince çağır)
     */
    public static function clearCache(string $type, int $id): void
    {
        Cache::forget("embedding:{$type}:{$id}");
    }
}