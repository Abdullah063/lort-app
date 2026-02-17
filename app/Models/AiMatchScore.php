<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiMatchScore extends Model
{
protected $fillable = [
        'user_id',
        'matched_user_id',
        'score',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:4',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function matchedUser()
    {
        return $this->belongsTo(User::class, 'matched_user_id');
    }
}
