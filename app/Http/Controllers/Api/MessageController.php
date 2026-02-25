<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Http\Requests\StartDirectMessageRequest;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\UserMatch;
use App\Services\LimitService;
use App\Services\NotificationService;
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

        // Direkt mesaj sohbetlerini de ekle (eşleşmesiz)
        $directConversations = Conversation::whereNull('match_id')
            ->whereHas('messages', function ($q) use ($user) {
                $q->where('sender_id', $user->id);
            })
            ->orWhere(function ($q) use ($user) {
                $q->whereNull('match_id')
                    ->where('started_by', $user->id);
            })
            ->orWhere(function ($q) use ($user) {
                $q->whereNull('match_id')
                    ->where('target_user_id', $user->id);
            })
            ->with(['messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->get()
            ->map(function ($conv) use ($user) {
                $otherUserId = $conv->started_by === $user->id ? $conv->target_user_id : $conv->started_by;
                $otherUser = User::with('entrepreneurProfile')->find($otherUserId);
                $lastMessage = $conv->messages->first();
                $unreadCount = Message::where('conversation_id', $conv->id)
                    ->where('sender_id', '!=', $user->id)
                    ->where('status', '!=', 'read')
                    ->count();

                return [
                    'match_id'        => null,
                    'conversation_id' => $conv->id,
                    'other_user'      => $otherUser,
                    'last_message'    => $lastMessage,
                    'unread_count'    => $unreadCount,
                    'matched_at'      => null,
                    'is_direct'       => true,
                ];
            });

        $all = collect($conversations->all())
            ->merge($directConversations->all())
            ->sortByDesc(function ($item) {
                return $item['last_message']?->created_at ?? '0';
            })
            ->values();

        return response()->json([
            'conversations' => $all,
            'count'         => $all->count(),
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
        if (!$this->canAccessConversation($user, $conversation)) {
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
    public function send(SendMessageRequest $request, $conversationId)
    {
        $user = auth('api')->user();

        $conversation = Conversation::with('userMatch')->find($conversationId);

        if (!$conversation) {
            return response()->json([
                'message' => 'Sohbet bulunamadı',
            ], 404);
        }

        // Bu sohbet bana ait mi
        if (!$this->canAccessConversation($user, $conversation)) {
            return response()->json([
                'message' => 'Bu sohbete erişim yetkiniz yok',
            ], 403);
        }

        // Mesaj limiti kontrolü (sadece direkt mesaj sohbetlerinde)
        if (!$conversation->match_id) {
            $limitCheck = LimitService::check($user->id, 'daily_message');
            if (!$limitCheck['allowed']) {
                return response()->json([
                    'message'   => $limitCheck['message'],
                    'remaining' => $limitCheck['remaining'],
                    'limit'     => $limitCheck['limit'],
                ], 429);
            }
        }

        $request->validated();

        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id'       => $user->id,
            'content'         => $request->content,
            'status'          => 'sent',
        ]);

        // Kullanımı artır (sadece direkt mesaj sohbetlerinde)
        if (!$conversation->match_id) {
            LimitService::increment($user->id, 'daily_message');
        }

        // Karşı tarafa bildirim gönder
        $otherUserId = $this->getOtherUserId($user, $conversation);

        NotificationService::sendDirect(
            $otherUserId,
            'message',
            'Yeni Mesaj',
            "{$user->name} size bir mesaj gönderdi"
        );

        return response()->json([
            'message'   => 'Mesaj gönderildi',
            'data'      => $message->load('sender:id,name,surname'),
            'remaining' => LimitService::remaining($user->id, 'daily_message'),
        ], 201);
    }

    // =============================================
    // DİREKT MESAJ BAŞLAT (Eşleşmesiz - Premium)
    // POST /api/conversations/direct
    // =============================================
    public function startDirect(StartDirectMessageRequest $request)
    {
        $user = auth('api')->user();

        // Premium özellik kontrolü
        $limitCheck = LimitService::check($user->id, 'direct_message');
        if (!$limitCheck['allowed']) {
            return response()->json([
                'message'   => $limitCheck['remaining'] === 0 && $limitCheck['limit'] > 0
                    ? $limitCheck['message']
                    : 'Direkt mesaj göndermek premium özelliğidir. Paketinizi yükseltin.',
                'remaining' => $limitCheck['remaining'],
                'limit'     => $limitCheck['limit'],
            ], $limitCheck['limit'] === 0 ? 403 : 429);
        }

        $request->validated();

        // Kendine mesaj atamaz
        if ($request->target_user_id == $user->id) {
            return response()->json([
                'message' => 'Kendini   ze mesaj gönderemezsiniz',
            ], 422);
        }

        // Zaten eşleşme var mı kontrol et
        $existingMatch = UserMatch::where(function ($q) use ($user, $request) {
            $q->where('user1_id', $user->id)->where('user2_id', $request->target_user_id);
        })->orWhere(function ($q) use ($user, $request) {
            $q->where('user1_id', $request->target_user_id)->where('user2_id', $user->id);
        })->first();

        if ($existingMatch && $existingMatch->conversation) {
            return response()->json([
                'message'         => 'Bu kullanıcıyla zaten eşleşmeniz var, mevcut sohbeti kullanın.',
                'conversation_id' => $existingMatch->conversation->id,
            ], 409);
        }

        // Daha önce direkt sohbet var mı
        $existingConversation = Conversation::whereNull('match_id')
            ->where(function ($q) use ($user, $request) {
                $q->where('started_by', $user->id)
                    ->where('target_user_id', $request->target_user_id);
            })->orWhere(function ($q) use ($user, $request) {
                $q->whereNull('match_id')
                    ->where('started_by', $request->target_user_id)
                    ->where('target_user_id', $user->id);
            })->first();

        if ($existingConversation) {
            return response()->json([
                'message'         => 'Bu kullanıcıyla zaten sohbetiniz var.',
                'conversation_id' => $existingConversation->id,
            ], 409);
        }

        // Mesaj limiti kontrolü
        $msgLimit = LimitService::check($user->id, 'daily_message');
        if (!$msgLimit['allowed']) {
            return response()->json([
                'message'   => $msgLimit['message'],
                'remaining' => $msgLimit['remaining'],
            ], 429);
        }

        // Sohbet oluştur
        $conversation = Conversation::create([
            'match_id'       => null,
            'started_by'     => $user->id,
            'target_user_id' => $request->target_user_id,
        ]);

        // İlk mesajı gönder
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id,
            'content'         => $request->content,
            'status'          => 'sent',
        ]);

        LimitService::increment($user->id, 'daily_message');
        LimitService::increment($user->id, 'direct_message');

        // Karşı tarafa bildirim
        NotificationService::sendDirect(
            $request->target_user_id,
            'message',
            'Yeni Mesaj',
            "{$user->name} size bir mesaj gönderdi"
        );

        return response()->json([
            'message'         => 'Sohbet başlatıldı',
            'conversation_id' => $conversation->id,
            'data'            => $message->load('sender:id,name,surname'),
        ], 201);
    }

    // =============================================
    // YARDIMCI: Sohbete erişim kontrolü
    // =============================================
    private function canAccessConversation($user, $conversation): bool
    {
        // Eşleşme bazlı sohbet
        if ($conversation->match_id) {
            $match = $conversation->userMatch;
            return $match && ($match->user1_id === $user->id || $match->user2_id === $user->id);
        }

        // Direkt mesaj sohbeti
        return $conversation->started_by === $user->id || $conversation->target_user_id === $user->id;
    }

    // =============================================
    // YARDIMCI: Karşı tarafın ID'si
    // =============================================
    private function getOtherUserId($user, $conversation): int
    {
        if ($conversation->match_id) {
            $match = $conversation->userMatch;
            return $match->user1_id === $user->id ? $match->user2_id : $match->user1_id;
        }

        return $conversation->started_by === $user->id
            ? $conversation->target_user_id
            : $conversation->started_by;
    }
}
