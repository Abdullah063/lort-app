<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMatch extends Model
{
protected $table = 'matches';

    protected $fillable = [
        'user1_id',
        'user2_id',
        'is_ai_match',
    ];

    protected function casts(): array
    {
        return [
            'matched_at' => 'datetime',
            'is_ai_match' => 'boolean',
        ];
    }

    public function user1()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    public function user2()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    // Eşleşmenin sohbeti
    public function conversation()
    {
        return $this->hasOne(Conversation::class, 'match_id');
    }
}
