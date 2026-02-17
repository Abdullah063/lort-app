<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\CssSelector\Node\FunctionNode;

class Membership extends Model
{
    protected $fillable = [
        'user_id',
        'package_id',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function packageDefinition()
    {
        return $this->belongsTo(PackageDefinition::class, 'package_id');
    }

    // Bu üyeliğe ait ödemeler
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Üyelik geçmişi
    public function history()
    {
        return $this->hasMany(MembershipHistory::class);
    }
    

}



























