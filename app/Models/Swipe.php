<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Swipe extends Model
{
   protected $fillable = [
        'swiper_id',
        'swiped_id',
        'type',
    ];

    // Kaydıran kullanıcı
    public function swiper()
    {
        return $this->belongsTo(User::class, 'swiper_id');
    }

    // Kaydırılan kullanıcı
    public function swiped()
    {
        return $this->belongsTo(User::class, 'swiped_id');
    }
}