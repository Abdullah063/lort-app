<?php

namespace App\Services;

use App\Events\NotificationSent;
use App\Models\User;
use App\Models\NotificationTemplate;
use App\Models\UserNotification;

class NotificationService
{

    public static function send(int $userId, string $templateCode, array $variables = []): ?UserNotification
    {
        $user = User::find($userId);
        if (!$user) return null;

        $limitCode = 'notify_' . $templateCode;
        $limitCheck = LimitService::check($userId, $limitCode);
        if (!$limitCheck['allowed'] && $limitCheck['remaining'] !== -1) {
            return null;
        }

        $variables['name'] = $variables['name'] ?? $user->name;

        // Varsayılan dilde şablonu çek
        $lang = app()->getLocale();
        $template = NotificationTemplate::where('template_code', $templateCode)
            ->where('language_code', $lang)
            ->first();

        if (!$template) {
            $template = NotificationTemplate::where('template_code', $templateCode)
                ->where('language_code', 'tr')
                ->first();
        }

        $title = $template ? self::replaceVariables($template->title, $variables) : $templateCode;
        $body  = $template ? self::replaceVariables($template->content, $variables) : null;

        $typeMap = [
            'welcome'          => 'welcome',
            'match'            => 'match',
            'package_congrats' => 'package',
            'receipt'          => 'receipt',
            'like'             => 'like',
            'super_like'       => 'super_like',
            'message'          => 'message',
        ];

        return UserNotification::create([
            'user_id'       => $userId,
            'type'          => $typeMap[$templateCode] ?? $templateCode,
            'template_code' => $templateCode,
            'variables'     => $variables,
            'title'         => $title,
            'body'          => $body,
            'is_read'       => false,
            'email_sent'    => false,
        ]);
        event(new NotificationSent($userId, [
            'id'    => $notif->id,
            'type'  => $notif->type,
            'title' => $notif->title,
            'body'  => $notif->body,
        ]));
        return $notif;
    }
    /**
     * Şablonsuz hızlı bildirim gönder
     * Özel durumlar için (şablona uymayan bildirimler)
     */
    public static function sendDirect(int $userId, string $type, string $title, string $body): ?UserNotification
    {
        $user = User::find($userId);
        if (!$user) return null;

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
            if (self::send($userId, $templateCode, $variables)) $count++;
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
