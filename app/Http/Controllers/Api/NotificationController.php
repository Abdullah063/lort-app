<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // =============================================
    // BİLDİRİMLERİMİ LİSTELE
    // GET /api/notifications
    // =============================================
    public function index(Request $request)
    {
        $user = auth('api')->user();

        $query = UserNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Sadece okunmamışlar
        if ($request->boolean('unread_only')) {
            $query->where('is_read', false);
        }

        $notifications = $query->paginate($request->per_page ?? 20);

        return response()->json($notifications);
    }

    // =============================================
    // OKUNMAMIŞ BİLDİRİM SAYISI
    // GET /api/notifications/unread-count
    // =============================================
    public function unreadCount()
    {
        $user = auth('api')->user();

        $count = UserNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'unread_count' => $count,
        ]);
    }

    // =============================================
    // BİLDİRİMİ OKUNDU İŞARETLE
    // PUT /api/notifications/{id}/read
    // =============================================
    public function markAsRead($id)
    {
        $user = auth('api')->user();

        $notification = UserNotification::where('user_id', $user->id)
            ->find($id);

        if (!$notification) {
            return response()->json([
                'message' => 'Bildirim bulunamadı',
            ], 404);
        }

        $notification->update(['is_read' => true]);

        return response()->json([
            'message' => 'Bildirim okundu',
        ]);
    }

    // =============================================
    // TÜM BİLDİRİMLERİ OKUNDU İŞARETLE
    // PUT /api/notifications/read-all
    // =============================================
    public function markAllAsRead()
    {
        $user = auth('api')->user();

        $updated = UserNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'message' => "{$updated} bildirim okundu işaretlendi",
        ]);
    }

    // =============================================
    // BİLDİRİM SİL
    // DELETE /api/notifications/{id}
    // =============================================
    public function destroy($id)
    {
        $user = auth('api')->user();

        $notification = UserNotification::where('user_id', $user->id)
            ->find($id);

        if (!$notification) {
            return response()->json([
                'message' => 'Bildirim bulunamadı',
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'message' => 'Bildirim silindi',
        ]);
    }
}