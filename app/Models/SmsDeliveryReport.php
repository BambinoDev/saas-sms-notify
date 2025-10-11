<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsDeliveryReport extends Model
{
    protected $fillable = [
        'sms_queue_id',
        'gateway_message_id',
        'gateway_status',
        'gateway_raw_payload',
        'reported_at',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
    ];

    // Relations
    public function smsQueue(): BelongsTo
    {
        return $this->belongsTo(SmsQueue::class, 'sms_queue_id');
    }
}
