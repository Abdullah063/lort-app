<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $fillable = [
        'user_id',
        'favorited_user_id'
    ];

    #favoriye ekleyen user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Favoriye eklenen kullanıcı
    public function favoritedUser()
    {
        return $this->belongsTo(User::class, 'favorited_user_id');
    }
}
