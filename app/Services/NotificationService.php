<?php

namespace App\Services;

use App\Models\User;
use App\Models\NotificationTemplate;
use App\Models\UserNotification;

class NotificationService
{

    public static function send(int $userId, string $templateCode, array $variables = [], ?string $lang = null): ?UserNotification
    {
        $user = User::find($userId);

        if (!$user) {
            return null;
        }

        // Paket bazlı bildirim kontrolü
        $limitCode = 'notify_' . $templateCode;
        $limitCheck = LimitService::check($userId, $limitCode);

        // Limit tanımlı ve kapalıysa gönderme
        // Not: Limit tanımsızsa (remaining = -1) göndeririz
        if (!$limitCheck['allowed'] && $limitCheck['remaining'] !== -1) {
            return null;
        }

        // Dil belirleme
        $langCode = $lang ?? 'en';

        // Şablonu bul
        $template = NotificationTemplate::where('template_code', $templateCode)
            ->where('language_code', $langCode)
            ->first();

        // Şablon bulunamazsa varsayılan dilde dene
        if (!$template) {
            $template = NotificationTemplate::where('template_code', $templateCode)
                ->where('language_code', 'en')
                ->first();
        }

        if (!$template) {
            return null;
        }

        // Değişkenleri doldur
        $variables['name'] = $variables['name'] ?? $user->name;

        $title = self::replaceVariables($template->title, $variables);
        $body = self::replaceVariables($template->content, $variables);

        // Bildirim tipini belirle
        $typeMap = [
            'welcome'          => 'welcome',
            'match'            => 'match',
            'package_congrats' => 'package',
            'receipt'          => 'receipt',
            'like'             => 'like',
            'super_like'       => 'super_like',
            'message'          => 'message',
        ];

        $type = $typeMap[$templateCode] ?? $templateCode;

        // DB'ye kaydet
        $notification = UserNotification::create([
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => $title,
            'body'       => $body,
            'is_read'    => false,
            'email_sent' => false,
        ]);

        // TODO: Email gönderimi (ileride)
        // self::sendEmail($user, $title, $body);

        // TODO: Push notification (ileride)
        // self::sendPush($user, $title, $body);

        return $notification;
    }

    /**
     * Şablonsuz hızlı bildirim gönder
     * Özel durumlar için (şablona uymayan bildirimler)
     */
    public static function sendDirect(int $userId, string $type, string $title, string $body): ?UserNotification
    {
        $user = User::find($userId);

        if (!$user) {
            return null;
        }

        return UserNotification::create([
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => $title,
            'body'       => $body,
            'is_read'    => false,
            'email_sent' => false,
        ]);
    }

    /**
     * Toplu bildirim gönder (tüm kullanıcılara veya belirli gruba)
     */
    public static function sendBulk(array $userIds, string $templateCode, array $variables = []): int
    {
        $count = 0;

        foreach ($userIds as $userId) {
            $result = self::send($userId, $templateCode, $variables);
            if ($result) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * {{değişken}} yerlerine gerçek değerleri yaz
     */
    private static function replaceVariables(string $text, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
        }

        return $text;
    }
}