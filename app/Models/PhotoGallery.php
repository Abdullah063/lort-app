<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhotoGallery extends Model
{
  protected $fillable = [
        'user_id',
        'image_url',
        'sort_order',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
