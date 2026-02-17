<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipHistory extends Model
{
protected $table = 'membership_history';

    protected $fillable = [
        'membership_id',
        'user_id',
        'previous_package_id',
        'new_package_id',
        'change_reason',
        'processed_by',
        'description',
    ];

    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function previousPackage()
    {
        return $this->belongsTo(PackageDefinition::class, 'previous_package_id');
    }

    public function newPackage()
    {
        return $this->belongsTo(PackageDefinition::class, 'new_package_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
