<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsGenerationLog extends Model
{
    protected $fillable = [
        'organization_id',
        'rule_id',
        'user_id',
        'trigger_type',
        'started_at',
        'completed_at',
        'duration_seconds',
        'status',
        'total_cases_evaluated',
        'total_sms_generated',
        'total_duplicates_skipped',
        'total_errors',
        'error_message',
        'error_trace',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration_seconds' => 'integer',
        'total_cases_evaluated' => 'integer',
        'total_sms_generated' => 'integer',
        'total_duplicates_skipped' => 'integer',
        'total_errors' => 'integer',
        'metadata' => 'array',
    ];

    // RELATIONS

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(SmsRule::class, 'rule_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
