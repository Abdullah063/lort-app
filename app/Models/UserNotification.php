<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $table = 'notifications';
    protected $fillable = [
        'user_id',
        'type',
        'template_code',
        'variables',
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
            'variables'  => 'array',
        ];
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
