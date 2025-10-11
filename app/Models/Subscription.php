<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'organization_id',
        'plan',
        'status',
        'sms_limit',
        'sms_used',
        'users_limit',
        'structures_limit',
        'price',
        'stripe_subscription_id',
        'current_period_start',
        'current_period_end',
        'trial_ends_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'trial_ends_at' => 'datetime',
        'price' => 'decimal:2',
        'sms_limit' => 'integer',
        'sms_used' => 'integer',
        'users_limit' => 'integer',
        'structures_limit' => 'integer',
    ];

    /**
     * Relation: Organization
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Check if SMS limit reached
     */
    public function hasReachedSmsLimit(): bool
    {
        return $this->sms_used >= $this->sms_limit;
    }

    /**
     * Get remaining SMS quota
     */
    public function remainingSmsQuota(): int
    {
        return max(0, $this->sms_limit - $this->sms_used);
    }

    /**
     * Get SMS usage percentage
     */
    public function smsUsagePercentage(): float
    {
        if ($this->sms_limit === 0) {
            return 0;
        }

        return round(($this->sms_used / $this->sms_limit) * 100, 2);
    }

    /**
     * Check if usage is near limit (>= 80%)
     */
    public function isNearSmsLimit(): bool
    {
        return $this->smsUsagePercentage() >= 80;
    }

    /**
     * Increment SMS usage
     */
    public function incrementSmsUsage(int $count = 1): void
    {
        $this->increment('sms_used', $count);
    }

    /**
     * Decrement SMS usage (for refunds/cancellations)
     */
    public function decrementSmsUsage(int $count = 1): void
    {
        $this->decrement('sms_used', max(0, $count));
    }

    /**
     * Reset monthly usage
     */
    public function resetMonthlyUsage(): void
    {
        $this->update(['sms_used' => 0]);
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               ($this->current_period_end === null || $this->current_period_end->isFuture());
    }

    /**
     * Check if subscription is on trial
     */
    public function isOnTrial(): bool
    {
        return $this->status === 'trial' &&
               $this->trial_ends_at !== null &&
               $this->trial_ends_at->isFuture();
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired(): bool
    {
        return $this->current_period_end !== null && 
               $this->current_period_end->isPast();
    }

    /**
     * Get days until renewal
     */
    public function daysUntilRenewal(): ?int
    {
        if ($this->current_period_end === null) {
            return null;
        }

        return max(0, now()->diffInDays($this->current_period_end, false));
    }

    /**
     * Check if users limit reached
     */
    public function hasReachedUsersLimit(): bool
    {
        $currentUsersCount = $this->organization->users()->count();
        return $currentUsersCount >= $this->users_limit;
    }

    /**
     * Check if structures limit reached
     */
    public function hasReachedStructuresLimit(): bool
    {
        $currentStructuresCount = $this->organization->cases()
            ->distinct('structure_sanitaire')
            ->count('structure_sanitaire');
        
        return $currentStructuresCount >= $this->structures_limit;
    }
}
