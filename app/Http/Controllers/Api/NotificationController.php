<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('api')->user();
        $lang = app()->getLocale();

        $query = UserNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($request->boolean('unread_only')) {
            $query->where('is_read', false);
        }

        $notifications = $query->paginate($request->per_page ?? 20);

        $notifications->getCollection()->transform(function ($notification) use ($lang) {
            return self::translateNotification($notification, $lang);
        });

        return response()->json($notifications);
    }

    public function unreadCount()
    {
        $user = auth('api')->user();

        $count = UserNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    public function markAsRead($id)
    {
        $user = auth('api')->user();
        $notification = UserNotification::where('user_id', $user->id)->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Bildirim bulunamadı'], 404);
        }

        $notification->update(['is_read' => true]);
        return response()->json(['message' => 'Bildirim okundu']);
    }

    public function markAllAsRead()
    {
        $user = auth('api')->user();

        $updated = UserNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => "{$updated} bildirim okundu"]);
    }

    public function destroy($id)
    {
        $user = auth('api')->user();
        $notification = UserNotification::where('user_id', $user->id)->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Bildirim bulunamadı'], 404);
        }

        $notification->delete();
        return response()->json(['message' => 'Bildirim silindi']);
    }

    private static function translateNotification($notification, $lang)
    {
        if (!$notification->template_code) {
            return $notification;
        }

        $template = NotificationTemplate::where('template_code', $notification->template_code)
            ->where('language_code', $lang)
            ->first();

        if (!$template) {
            $template = NotificationTemplate::where('template_code', $notification->template_code)
                ->where('language_code', 'en')
                ->first();
        }

        if ($template) {
            $variables = $notification->variables ?? [];
            $notification->title = self::replaceVariables($template->title, $variables);
            $notification->body = self::replaceVariables($template->content, $variables);
        }

        return $notification;
    }

    private static function replaceVariables(string $text, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
        }
        return $text;
    }
}