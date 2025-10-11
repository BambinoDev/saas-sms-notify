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
        
        // Onboarding - Company Info
        'organization_type',
        'sector',
        'timezone',
        'team_size',
        
        // Onboarding - CommCare Config
        'commcare_domain',
        'commcare_project_name',
        'commcare_api_key',
        'commcare_app_id',
        
        // Onboarding - Phone Validation
        'primary_country',
        'allowed_prefixes',
        'phone_validation_mode',
        'mobile_only',
        'auto_format_e164',
        
        // Onboarding - Field Mappings
        'field_mappings',
        
        // Onboarding - Tracking
        'onboarding_completed',
        'onboarding_completed_at',
        'onboarding_step',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'trial_ends_at' => 'datetime',
        'settings' => 'array',
        'allowed_prefixes' => 'array',
        'field_mappings' => 'array',
        'onboarding_completed' => 'boolean',
        'onboarding_completed_at' => 'datetime',
        'mobile_only' => 'boolean',
        'auto_format_e164' => 'boolean',
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

    /**
     * Check if onboarding is completed
     */
    public function hasCompletedOnboarding(): bool
    {
        return $this->onboarding_completed === true;
    }

    /**
     * Mark onboarding as completed
     */
    public function completeOnboarding(): void
    {
        $this->update([
            'onboarding_completed' => true,
            'onboarding_completed_at' => now(),
            'onboarding_step' => 6, // Dernière étape
        ]);
    }

    /**
     * Update onboarding step
     */
    public function updateOnboardingStep(int $step): void
    {
        $this->update(['onboarding_step' => $step]);
    }

    /**
     * Get onboarding progress percentage
     */
    public function onboardingProgress(): int
    {
        if ($this->onboarding_completed) {
            return 100;
        }
        
        // 6 étapes total
        return (int) (($this->onboarding_step / 6) * 100);
    }

    /**
     * Check if CommCare is configured
     */
    public function hasCommCareConfig(): bool
    {
        return !empty($this->commcare_domain) && 
               !empty($this->commcare_api_key);
    }

    /**
     * Check if phone validation is configured
     */
    public function hasPhoneValidationConfig(): bool
    {
        return !empty($this->primary_country) && 
               !empty($this->allowed_prefixes);
    }

    /**
     * Check if field mappings are configured
     */
    public function hasFieldMappings(): bool
    {
        return !empty($this->field_mappings) && 
               is_array($this->field_mappings) &&
               count($this->field_mappings) > 0;
    }
}

