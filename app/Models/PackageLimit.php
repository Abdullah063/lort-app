<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageLimit extends Model
{
protected $fillable = [
        'package_id',
        'limit_code',
        'limit_name',
        'limit_value',
        'period',
        'is_active',
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
