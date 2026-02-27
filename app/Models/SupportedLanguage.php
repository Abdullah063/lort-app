<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportedLanguage extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
    public function profiles()
    {
        return $this->hasMany(EntrepreneurProfile::class, 'preferred_language', 'code');
    }
}
