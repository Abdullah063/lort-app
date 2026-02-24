<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'membership_id',
        'saved_card_id',
        'amount',
        'currency',
        'installment',
        'status',
        'payment_method',
        'provider_ref',
        'provider_meta',
        'error_code',
        'error_message',
        'receipt_sent',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'receipt_sent' => 'boolean',
            'provider_meta' => 'array',  // JSON otomatik array'e çevrilir
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

    public function savedCard()
    {
        return $this->belongsTo(SavedCard::class);
    }

    public function refundRequests()
    {
        return $this->hasMany(RefundRequest::class);
    }
}