<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageFeature extends Model
{
protected $fillable = [
        'package_id',
        'feature_code',
        'feature_name',
        'value',
        'value_type',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function packageDefinition()
    {
        return $this->belongsTo(PackageDefinition::class, 'package_id');
    }
}
