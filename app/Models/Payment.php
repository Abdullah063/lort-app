<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
protected $fillable = [
        'user_id',
        'membership_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'provider_ref',
        'receipt_sent',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'receipt_sent' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }

    // Bu ödemeye ait iade talepleri
    public function refundRequests()
    {
        return $this->hasMany(RefundRequest::class);
    }
}
