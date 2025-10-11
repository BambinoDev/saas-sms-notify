<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantCase extends Model
{
    use SoftDeletes;

    protected $table = 'cases';

    protected $fillable = [
        'case_id',
        'case_type',
        'case_name',
        'external_id',
        'contact_phone_number',
        'formatted_phone_number',
        'phone_valid',
        'phone_validation_error',
        'primary_date',
        'secondary_date',
        'sequence_number',
        'owner_id',
        'owner_name',
        'location_id',
        'location_name',
        'case_opened_at',
        'case_closed_at',
        'is_closed',
        'is_eligible_for_sms',
        'properties',
        'last_synced_at',
        'sync_batch_id',
    ];

    protected $casts = [
        'phone_valid' => 'boolean',
        'primary_date' => 'date',
        'secondary_date' => 'date',
        'case_opened_at' => 'datetime',
        'case_closed_at' => 'datetime',
        'is_closed' => 'boolean',
        'is_eligible_for_sms' => 'boolean',
        'properties' => 'array',
        'last_synced_at' => 'datetime',
    ];

    // Relations
    public function smsQueue(): HasMany
    {
        return $this->hasMany(SmsQueue::class, 'case_id');
    }

    // Scopes
    public function scopeEligibleForSms($query)
    {
        return $query->where('is_eligible_for_sms', true)
            ->where('phone_valid', true)
            ->whereNotNull('primary_date');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('case_type', $type);
    }

    public function scopeOpen($query)
    {
        return $query->where('is_closed', false);
    }

    public function scopeUpcoming($query, $days = 30)
    {
        return $query->whereNotNull('primary_date')
            ->whereBetween('primary_date', [now(), now()->addDays($days)]);
    }

    // Methods
    public function getFullNameAttribute(): string
    {
        return $this->case_name;
    }

    public function getFormattedPhoneAttribute(): string
    {
        return $this->formatted_phone_number ?? $this->contact_phone_number ?? '';
    }

    public function isEligibleForSms(): bool
    {
        return $this->is_eligible_for_sms && $this->phone_valid && !is_null($this->primary_date);
    }
}
