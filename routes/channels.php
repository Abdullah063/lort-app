<?php

use Illuminate\Support\Facades\Broadcast;

// Kullanıcının kendi bildirim kanalı
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Sohbet kanalı (sadece eşleşen kişiler erişebilir)
Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    return \App\Models\Conversation::whereHas('match', function ($q) use ($user) {
        $q->where('user1_id', $user->id)
          ->orWhere('user2_id', $user->id);
    })->where('id', $conversationId)->exists();
});