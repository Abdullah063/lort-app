<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundRequest extends Model
{
protected $fillable = [
        'payment_id',
        'user_id',
        'reason',
        'description',
        'refund_amount',
        'status',
        'admin_note',
        'processed_by',
        'refund_ref',
    ];

    protected function casts(): array
    {
        return [
            'refund_amount' => 'decimal:2',
            'requested_at' => 'datetime',
            'decided_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // İadeyi işleyen admin
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}