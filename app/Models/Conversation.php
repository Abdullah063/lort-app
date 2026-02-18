<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'match_id',
        'started_by',
        'target_user_id',
    ];

    // Eşleşme bazlı sohbet
    public function userMatch()
    {
        return $this->belongsTo(UserMatch::class, 'match_id');
    }

    // Direkt mesajı başlatan
    public function starter()
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    // Direkt mesajın hedefi
    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    // Sohbetteki mesajlar
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}