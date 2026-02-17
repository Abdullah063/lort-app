<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\UserMatch;
use App\Models\UserNotification;
use App\Services\LimitService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    // =============================================
    // SOHBETLERİMİ LİSTELE
    // GET /api/conversations
    // =============================================
    public function conversations()
    {
        $user = auth('api')->user();

        $matches = UserMatch::where('user1_id', $user->id)
            ->orWhere('user2_id', $user->id)
            ->with(['conversation.messages' => function ($q) {
                $q->latest()->limit(1);
            }, 'user1.entrepreneurProfile', 'user2.entrepreneurProfile'])
            ->orderBy('matched_at', 'desc')
            ->get();

        // Karşı tarafı ve son mesajı ekle
        $conversations = $matches->map(function ($match) use ($user) {
            $otherUser = $match->user1_id === $user->id ? $match->user2 : $match->user1;
            $lastMessage = $match->conversation?->messages->first();
            $unreadCount = $match->conversation
                ? Message::where('conversation_id', $match->conversation->id)
                    ->where('sender_id', '!=', $user->id)
                    ->where('status', '!=', 'read')
                    ->count()
                : 0;

            return [
                'match_id'        => $match->id,
                'conversation_id' => $match->conversation?->id,
                'other_user'      => $otherUser,
                'last_message'    => $lastMessage,
                'unread_count'    => $unreadCount,
                'matched_at'      => $match->matched_at,
            ];
        });

        return response()->json([
            'conversations' => $conversations,
            'count'         => $conversations->count(),
        ]);
    }

    // =============================================
    // BİR SOHBETİN MESAJLARINI GETİR
    // GET /api/conversations/{conversationId}/messages
    // =============================================
    public function messages($conversationId)
    {
        $user = auth('api')->user();

        $conversation = Conversation::with('userMatch')->find($conversationId);

        if (!$conversation) {
            return response()->json([
                'message' => 'Sohbet bulunamadı',
            ], 404);
        }

        // Bu sohbet bana ait mi
        $match = $conversation->userMatch;
        if ($match->user1_id !== $user->id && $match->user2_id !== $user->id) {
            return response()->json([
                'message' => 'Bu sohbete erişim yetkiniz yok',
            ], 403);
        }

        // Karşı tarafın mesajlarını okundu yap
        Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $user->id)
            ->where('status', '!=', 'read')
            ->update(['status' => 'read']);

        $messages = Message::where('conversation_id', $conversationId)
            ->with('sender:id,name,surname')
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        return response()->json($messages);
    }

    // =============================================
    // MESAJ GÖNDER
    // POST /api/conversations/{conversationId}/messages
    // =============================================
    public function send(Request $request, $conversationId)
    {
        $user = auth('api')->user();

        $conversation = Conversation::with('userMatch')->find($conversationId);

        if (!$conversation) {
            return response()->json([
                'message' => 'Sohbet bulunamadı',
            ], 404);
        }

        // Bu sohbet bana ait mi
        $match = $conversation->userMatch;
        if ($match->user1_id !== $user->id && $match->user2_id !== $user->id) {
            return response()->json([
                'message' => 'Bu sohbete erişim yetkiniz yok',
            ], 403);
        }

        // Mesaj limiti kontrolü
        $limitCheck = LimitService::check($user->id, 'daily_message');
        if (!$limitCheck['allowed']) {
            return response()->json([
                'message'   => $limitCheck['message'],
                'remaining' => $limitCheck['remaining'],
                'limit'     => $limitCheck['limit'],
            ], 429);
        }

        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id'       => $user->id,
            'content'         => $request->content,
            'status'          => 'sent',
        ]);

        // Kullanımı artır
        LimitService::increment($user->id, 'daily_message');

        // Karşı tarafa bildirim gönder
        $otherUserId = $match->user1_id === $user->id ? $match->user2_id : $match->user1_id;

        UserNotification::create([
            'user_id'    => $otherUserId,
            'type'       => 'message',
            'title'      => 'Yeni Mesaj',
            'body'       => "{$user->name} size bir mesaj gönderdi",
            'is_read'    => false,
            'email_sent' => false,
        ]);

        return response()->json([
            'message'   => 'Mesaj gönderildi',
            'data'      => $message->load('sender:id,name,surname'),
            'remaining' => LimitService::remaining($user->id, 'daily_message'),
        ], 201);
    }
}