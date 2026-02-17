<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
protected $fillable = [
        'template_code',
        'language_code',
        'title',
        'content',
    ];
}