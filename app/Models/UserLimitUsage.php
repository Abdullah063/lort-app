<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLimitUsage extends Model
{
    protected $fillable = [
        'user_id',
        'limit_code',
        'usage_count',
        'period_start',
        'last_usage_at',
    ];

    protected function casts(): array
    {
        return [
            'last_usage_at' => 'datetime',
            
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}