<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
protected $fillable = ['match_id'];

    // Bu sohbet hangi eşleşmeye ait
    public function userMatch()
    {
        return $this->belongsTo(UserMatch::class, 'match_id');
    }

    // Sohbetteki mesajlar
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}