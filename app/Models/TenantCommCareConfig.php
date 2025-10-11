<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantCommCareConfig extends Model
{
    protected $table = 'tenant_commcare_configs';
    protected $fillable = [
        'tenant_id',
        'commcare_domain',
        'commcare_api_key',
        'commcare_username',
        'commcare_project_name',
        'available_case_types',
        'default_case_type',
        'include_closed_cases',
        'sync_batch_size',
        'sync_schedule_time',
        'auto_sync_enabled',
        'connection_valid',
        'last_connection_test',
        'last_successful_sync',
        'total_cases_synced',
        'last_sync_error',
    ];

    protected $casts = [
        'available_case_types' => 'array',
        'include_closed_cases' => 'boolean',
        'auto_sync_enabled' => 'boolean',
        'connection_valid' => 'boolean',
        'last_connection_test' => 'datetime',
        'last_successful_sync' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // Accessors
    public function getCommcareApiKeyAttribute($value)
    {
        return decrypt($value);
    }

    // Mutators
    public function setCommcareApiKeyAttribute($value)
    {
        $this->attributes['commcare_api_key'] = encrypt($value);
    }
}
