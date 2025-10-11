<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantOnboarding extends Model
{
    protected $fillable = [
        'tenant_id',
        'current_step',
        'step_welcome_completed',
        'step_commcare_api_completed',
        'step_field_mapping_completed',
        'step_phone_config_completed',
        'step_sync_setup_completed',
        'step_sms_templates_completed',
        'started_at',
        'completed_at',
        'completion_percentage',
    ];

    protected $casts = [
        'step_welcome_completed' => 'boolean',
        'step_commcare_api_completed' => 'boolean',
        'step_field_mapping_completed' => 'boolean',
        'step_phone_config_completed' => 'boolean',
        'step_sync_setup_completed' => 'boolean',
        'step_sms_templates_completed' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relations
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // Methods
    public function isCompleted(): bool
    {
        return $this->completion_percentage === 100;
    }

    public function getNextStep(): ?string
    {
        $steps = [
            'welcome',
            'commcare_api',
            'field_mapping',
            'phone_config',
            'sync_setup',
            'sms_templates',
            'completed',
        ];

        $currentIndex = array_search($this->current_step, $steps);
        
        if ($currentIndex === false || $currentIndex >= count($steps) - 1) {
            return null;
        }

        return $steps[$currentIndex + 1];
    }
}
