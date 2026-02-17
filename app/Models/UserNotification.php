<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $table = 'notifications';
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'is_read',
        'email_sent',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'email_sent' => 'boolean',
        ];
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
