<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageDefinition extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'monthly_price',
        'yearly_price',
        'currency',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'yearly_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // Paketin özellikleri
    public function features()
    {
        return $this->hasMany(PackageFeature::class, 'package_id');
    }

    // Paketin limitleri
    public function limits()
    {
        return $this->hasMany(PackageLimit::class, 'package_id');
    }

    // Bu pakete sahip üyelikler
    public function memberships()
    {
        return $this->hasMany(Membership::class, 'package_id');
    }
}
