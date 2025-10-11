<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'domain',
        'logo_url',
        'primary_color',
        'secondary_color',
        'status',
        'trial_ends_at',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'trial_ends_at' => 'datetime',
        'settings' => 'array',
    ];

    /**
     * Relation: Users de l'organisation
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Relation: Subscription active
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latest();
    }

    /**
     * Relation: Toutes les subscriptions
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Relation: Cases (Women)
     */
    public function cases(): HasMany
    {
        return $this->hasMany(CaseModel::class);
    }

    /**
     * Relation: SMS Queue
     */
    public function smsQueue(): HasMany
    {
        return $this->hasMany(SmsQueue::class);
    }

    /**
     * Relation: Rules
     */
    public function rules(): HasMany
    {
        return $this->hasMany(SmsRule::class);
    }

    /**
     * Check if organization is on trial
     */
    public function isOnTrial(): bool
    {
        return $this->status === 'trial' && 
               $this->trial_ends_at !== null && 
               $this->trial_ends_at->isFuture();
    }

    /**
     * Check if subscription is active
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription !== null && 
               $this->subscription->status === 'active';
    }

    /**
     * Check if organization is active (active status or valid trial)
     */
    public function isActive(): bool
    {
        return $this->status === 'active' || 
               ($this->status === 'trial' && $this->isOnTrial()) ||
               $this->hasActiveSubscription();
    }

    /**
     * Get remaining trial days
     */
    public function remainingTrialDays(): ?int
    {
        if ($this->trial_ends_at === null) {
            return null;
        }

        return max(0, $this->trial_ends_at->diffInDays(now(), false));
    }

    /**
     * Get organization owner(s)
     */
    public function owners(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'owner');
    }

    /**
     * Get organization admins (including owners)
     */
    public function admins(): BelongsToMany
    {
        return $this->users()->whereIn('role', ['owner', 'admin']);
    }
}

