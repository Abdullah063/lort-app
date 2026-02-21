<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\PackageDefinition;
use App\Models\PackageLimit;
use App\Models\UserLimitUsage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LimitService
{
    // ──────────────────────────────────────────────
    // 1) YARDIMCI METODLAR
    // ──────────────────────────────────────────────

    /**
     * Kullanıcının aktif paket ID'sini döndürür.
     * 1 saat Redis'te cache'lenir.
     */
    private static function getActivePackageId(int $userId): int
    {
        return Cache::remember("package:user:{$userId}", 3600, function () use ($userId) {
            $membership = Membership::where('user_id', $userId)
                ->where('is_active', true)
                ->first();

            return $membership
                ? $membership->package_id
                : (PackageDefinition::where('name', 'free')->value('id') ?? 1);
        });
    }

    /**
     * Paket limitini döndürür.
     * 1 saat Redis'te cache'lenir.
     */
    private static function getPackageLimit(int $packageId, string $limitCode): ?PackageLimit
    {
        // Cache null değer de tutabilsin diye remember yerine manual yapıyoruz
        $cacheKey = "pkg_limit:{$packageId}:{$limitCode}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $limit = PackageLimit::where('package_id', $packageId)
            ->where('limit_code', $limitCode)
            ->where('is_active', true)
            ->first();

        // null olsa bile cache'le (DB'ye gereksiz sorgu atmasın)
        Cache::put($cacheKey, $limit, 3600);

        return $limit;
    }

    /**
     * Periyoda göre başlangıç tarihini döndürür.
     */
    private static function getPeriodStart(string $period): string
    {
        return match ($period) {
            'daily'   => now()->startOfDay()->toDateTimeString(),
            'weekly'  => now()->startOfWeek()->toDateTimeString(),
            'monthly' => now()->startOfMonth()->toDateTimeString(),
            'total'   => '2000-01-01 00:00:00',
            default   => now()->startOfDay()->toDateTimeString(),
        };
    }

    /**
     * Kullanım sayısı için Redis key
     */
    private static function usageCacheKey(int $userId, string $limitCode, string $period): string
    {
        $suffix = match ($period) {
            'daily'   => now()->format('Y-m-d'),
            'weekly'  => now()->startOfWeek()->format('Y-m-d'),
            'monthly' => now()->format('Y-m'),
            'total'   => 'total',
            default   => now()->format('Y-m-d'),
        };

        return "usage:{$userId}:{$limitCode}:{$suffix}";
    }

    /**
     * Periyot bitişine kalan saniye (TTL için)
     */
    private static function periodTtl(string $period): int
    {
        return match ($period) {
            'daily'   => (int) now()->diffInSeconds(now()->endOfDay()),
            'weekly'  => (int) now()->diffInSeconds(now()->endOfWeek()),
            'monthly' => (int) now()->diffInSeconds(now()->endOfMonth()),
            'total'   => 60 * 60 * 24 * 365,  // 1 yıl
            default   => (int) now()->diffInSeconds(now()->endOfDay()),
        };
    }

    // ──────────────────────────────────────────────
    // 2) KULLANIM SAYISI OKUMA (Redis → DB fallback)
    // ──────────────────────────────────────────────

    /**
     * Kullanım sayısını önce Redis'ten okur.
     * Redis'te yoksa DB'den alır ve Redis'e yazar.
     */
    private static function getUsageCount(int $userId, string $limitCode, string $period): int
    {
        $cacheKey = self::usageCacheKey($userId, $limitCode, $period);

        // Redis'te varsa direkt dön
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return (int) $cached;
        }

        // Yoksa DB'den al
        $periodStart = self::getPeriodStart($period);
        $usage = UserLimitUsage::where('user_id', $userId)
            ->where('limit_code', $limitCode)
            ->where('period_start', $periodStart)
            ->first();

        $count = $usage ? $usage->usage_count : 0;

        // Redis'e yaz (periyot sonuna kadar)
        Cache::put($cacheKey, $count, self::periodTtl($period));

        return $count;
    }

    // ──────────────────────────────────────────────
    // 3) ANA METODLAR
    // ──────────────────────────────────────────────

    /**
     * Kullanıcı bu işlemi yapabilir mi?
     */
    public static function check(int $userId, string $limitCode): array
    {
        $packageId = self::getActivePackageId($userId);
        $limit = self::getPackageLimit($packageId, $limitCode);

        // Limit tanımı yoksa → izin ver
        if (!$limit) {
            return ['allowed' => true, 'remaining' => -1, 'limit' => -1, 'message' => null];
        }

        // -1 = sınırsız
        if ($limit->limit_value == -1) {
            return ['allowed' => true, 'remaining' => -1, 'limit' => -1, 'message' => null];
        }

        // 0 = tamamen kapalı
        if ($limit->limit_value == 0) {
            return [
                'allowed'   => false,
                'remaining' => 0,
                'limit'     => 0,
                'message'   => "Bu özellik paketinizde bulunmuyor. Paketinizi yükseltin.",
            ];
        }

        // Kullanım sayısını oku (Redis → DB fallback)
        $used = self::getUsageCount($userId, $limitCode, $limit->period);
        $remaining = max(0, $limit->limit_value - $used);

        if ($remaining <= 0) {
            return [
                'allowed'   => false,
                'remaining' => 0,
                'limit'     => $limit->limit_value,
                'message'   => "{$limit->limit_name} limitinize ulaştınız ({$limit->limit_value}).",
            ];
        }

        return [
            'allowed'   => true,
            'remaining' => $remaining,
            'limit'     => $limit->limit_value,
            'message'   => null,
        ];
    }

    /**
     * Kullanım sayısını 1 artırır.
     * Redis'i HEMEN günceller, DB'yi de HEMEN günceller.
     */
    public static function increment(int $userId, string $limitCode): void
    {
        $packageId = self::getActivePackageId($userId);
        $limit = self::getPackageLimit($packageId, $limitCode);

        if (!$limit || $limit->limit_value == -1) {
            return;
        }

        $period = $limit->period;
        $periodStart = self::getPeriodStart($period);
        $cacheKey = self::usageCacheKey($userId, $limitCode, $period);

        // ── Redis'i güncelle ──
        $currentCache = Cache::get($cacheKey);
        if ($currentCache !== null) {
            Cache::put($cacheKey, (int) $currentCache + 1, self::periodTtl($period));
        } else {
            // Redis'te yoksa DB'den sayıyı al, +1 ekle, cache'e yaz
            $dbCount = UserLimitUsage::where('user_id', $userId)
                ->where('limit_code', $limitCode)
                ->where('period_start', $periodStart)
                ->value('usage_count') ?? 0;

            Cache::put($cacheKey, $dbCount + 1, self::periodTtl($period));
        }

        // ── DB'yi güncelle ──
        $affected = UserLimitUsage::where('user_id', $userId)
            ->where('limit_code', $limitCode)
            ->where('period_start', $periodStart)
            ->update([
                'usage_count'   => DB::raw('usage_count + 1'),
                'last_usage_at' => now(),
            ]);

        if ($affected === 0) {
            UserLimitUsage::create([
                'user_id'       => $userId,
                'limit_code'    => $limitCode,
                'period_start'  => $periodStart,
                'usage_count'   => 1,
                'last_usage_at' => now(),
            ]);
        }
    }

    /**
     * Kullanım sayısını 1 azaltır.
     */
    public static function decrement(int $userId, string $limitCode): void
    {
        $packageId = self::getActivePackageId($userId);
        $limit = self::getPackageLimit($packageId, $limitCode);

        if (!$limit || $limit->limit_value == -1) {
            return;
        }

        $period = $limit->period;
        $periodStart = self::getPeriodStart($period);
        $cacheKey = self::usageCacheKey($userId, $limitCode, $period);

        // ── Redis'i güncelle ──
        $currentCache = Cache::get($cacheKey);
        if ($currentCache !== null && (int) $currentCache > 0) {
            Cache::put($cacheKey, (int) $currentCache - 1, self::periodTtl($period));
        }

        // ── DB'yi güncelle ──
        $usage = UserLimitUsage::where('user_id', $userId)
            ->where('limit_code', $limitCode)
            ->where('period_start', $periodStart)
            ->first();

        if ($usage && $usage->usage_count > 0) {
            $usage->decrement('usage_count');
        }
    }

    /**
     * Kalan hakkı döndürür.
     */
    public static function remaining(int $userId, string $limitCode): int
    {
        return self::check($userId, $limitCode)['remaining'];
    }

    /**
     * Kullanıcının paket cache'ini temizle.
     * Paket değiştiğinde çağır!
     */
    public static function clearUserCache(int $userId): void
    {
        Cache::forget("package:user:{$userId}");

        // İsteğe bağlı: kullanım cache'lerini de temizleyebilirsin
        // ama periyot bitince zaten silinecekler
    }

    /**
     * Paket limit cache'ini temizle.
     * Admin panelinden limit değiştiğinde çağır!
     */
    public static function clearPackageLimitCache(int $packageId, string $limitCode): void
    {
        Cache::forget("pkg_limit:{$packageId}:{$limitCode}");
    }
}