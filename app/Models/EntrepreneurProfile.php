<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntrepreneurProfile extends Model
{
    protected $fillable = [
        'user_id',
        'category',
        'profile_image_url',
        'birth_date',
        'about_me',
        'is_online',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_online' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
