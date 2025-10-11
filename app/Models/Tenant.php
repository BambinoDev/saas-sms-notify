<?php

declare(strict_types=1);

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase;

    protected $table = 'tenants';
    
    protected $keyType = 'string';
    
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'slug',
        'database_schema',
        'status',
        'industry',
        'country_iso',
        'timezone',
        'trial_ends_at',
        'suspended_at',
        'activated_at',
        'data',
    ];

    protected $casts = [
        'id' => 'string',
        'trial_ends_at' => 'datetime',
        'suspended_at' => 'datetime',
        'activated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'slug',
            'database_schema',
            'status',
            'industry',
            'country_iso',
            'timezone',
            'trial_ends_at',
            'suspended_at',
            'activated_at',
        ];
    }

    public function getInternal($key)
    {
        return $this->getAttribute($key);
    }

    public function setInternal($key, $value)
    {
        return $this->setAttribute($key, $value);
    }

    public function getTenantKeyName(): string
    {
        return 'id';
    }

    public function getTenantKey()
    {
        return $this->getAttribute($this->getTenantKeyName());
    }

    public function getDatabaseName(): string
    {
        return config('database.connections.tenant.database', config('database.connections.pgsql.database'));
    }

    // Relations centrales
    public function commcareConfig()
    {
        return $this->hasOne(TenantCommCareConfig::class, 'tenant_id');
    }

    public function phoneConfig()
    {
        return $this->hasOne(TenantPhoneConfig::class, 'tenant_id');
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class, 'tenant_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'tenant_users')
            ->withPivot('role', 'invited_at', 'joined_at', 'last_accessed_at')
            ->withTimestamps();
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'tenant_id');
    }

    public function onboarding()
    {
        return $this->hasOne(TenantOnboarding::class, 'tenant_id');
    }
}
