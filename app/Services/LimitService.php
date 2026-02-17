<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\PackageDefinition;
use App\Models\PackageLimit;
use App\Models\UserLimitUsage;

class LimitService
{
    /**
     * Kullanıcının aktif paket ID'sini döndürür.
     * Üyeliği yoksa free paketi verir.
     */
    private static function getActivePackageId(int $userId): int
    {
        $membership = Membership::where('user_id', $userId)
            ->where('is_active', true)
            ->first();

        if ($membership) {
            return $membership->package_id;
        }

        return PackageDefinition::where('name', 'free')->value('id') ?? 1;
    }

    /**
     * Periyoda göre başlangıç tarihini STRING olarak döndürür.
     * Böylece DB karşılaştırması her zaman tutarlı olur.
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
     * Kullanıcı bu işlemi yapabilir mi?
     *
     * Kullanım:
     *   LimitService::check($userId, 'daily_like')
     *   LimitService::check($userId, 'daily_super_like')
     *   LimitService::check($userId, 'gallery_limit')
     *   LimitService::check($userId, 'listing_limit')
     *   LimitService::check($userId, 'daily_message')
     *   LimitService::check($userId, 'see_who_liked')
     */
    public static function check(int $userId, string $limitCode): array
    {
        $packageId = self::getActivePackageId($userId);

        $limit = PackageLimit::where('package_id', $packageId)
            ->where('limit_code', $limitCode)
            ->where('is_active', true)
            ->first();

        // Limit tanımı yoksa → izin ver
        if (!$limit) {
            return [
                'allowed'   => true,
                'remaining' => -1,
                'limit'     => -1,
                'message'   => null,
            ];
        }

        // -1 = sınırsız
        if ($limit->limit_value == -1) {
            return [
                'allowed'   => true,
                'remaining' => -1,
                'limit'     => -1,
                'message'   => null,
            ];
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

        // Kullanım sayısını kontrol et
        $periodStart = self::getPeriodStart($limit->period);

        $usage = UserLimitUsage::where('user_id', $userId)
            ->where('limit_code', $limitCode)
            ->whereRaw("period_start = ?", [$periodStart])
            ->first();

        $used = $usage ? $usage->usage_count : 0;
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
     * İşlem başarılı olduktan SONRA çağır.
     */
    public static function increment(int $userId, string $limitCode): void
    {
        $packageId = self::getActivePackageId($userId);

        $limit = PackageLimit::where('package_id', $packageId)
            ->where('limit_code', $limitCode)
            ->where('is_active', true)
            ->first();

        if (!$limit || $limit->limit_value == -1) {
            return;
        }

        $periodStart = self::getPeriodStart($limit->period);

        $affected = UserLimitUsage::where('user_id', $userId)
            ->where('limit_code', $limitCode)
            ->whereRaw("period_start = ?", [$periodStart])
            ->update([
                'usage_count'   => \DB::raw('usage_count + 1'),
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
     * Kullanım sayısını 1 azaltır (fotoğraf/ilan silme için).
     */
    public static function decrement(int $userId, string $limitCode): void
    {
        $packageId = self::getActivePackageId($userId);

        $limit = PackageLimit::where('package_id', $packageId)
            ->where('limit_code', $limitCode)
            ->where('is_active', true)
            ->first();

        if (!$limit || $limit->limit_value == -1) {
            return;
        }

        $periodStart = self::getPeriodStart($limit->period);

        $usage = UserLimitUsage::where('user_id', $userId)
            ->where('limit_code', $limitCode)
            ->whereRaw("period_start = ?", [$periodStart])
            ->first();

        if ($usage && $usage->usage_count > 0) {
            $usage->update([
                'usage_count' => $usage->usage_count - 1,
            ]);
        }
    }

    /**
     * Kalan hakkı döndürür. Frontend'de göstermek için.
     * -1 = sınırsız
     */
    public static function remaining(int $userId, string $limitCode): int
    {
        $result = self::check($userId, $limitCode);
        return $result['remaining'];
    }
}