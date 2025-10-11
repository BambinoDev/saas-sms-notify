<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
        'is_superadmin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_superadmin' => 'boolean',
        ];
    }

    // Relations
    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'tenant_users')
            ->withPivot('role', 'invited_at', 'joined_at', 'last_accessed_at')
            ->withTimestamps();
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Relations with Organizations
    
    /**
     * Relation: Organizations de l'utilisateur
     */
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get current organization
     */
    public function currentOrganization()
    {
        return $this->belongsTo(Organization::class, 'current_organization_id');
    }

    // Helper methods - Tenants
    public function hasAccessToTenant($tenantId): bool
    {
        return $this->tenants()->where('tenant_id', $tenantId)->exists();
    }

    public function isOwnerOf($tenantId): bool
    {
        return $this->tenants()
            ->where('tenant_id', $tenantId)
            ->wherePivot('role', 'owner')
            ->exists();
    }

    // Helper methods - Organizations
    
    /**
     * Check if user belongs to organization
     */
    public function belongsToOrganization(Organization $organization): bool
    {
        return $this->organizations()->where('organizations.id', $organization->id)->exists();
    }

    /**
     * Get role in organization
     */
    public function roleInOrganization(Organization $organization): ?string
    {
        $pivot = $this->organizations()
            ->where('organizations.id', $organization->id)
            ->first()?->pivot;

        return $pivot?->role;
    }

    /**
     * Check if user is owner of organization
     */
    public function isOwnerOfOrganization(Organization $organization): bool
    {
        return $this->roleInOrganization($organization) === 'owner';
    }

    /**
     * Check if user is admin of organization
     */
    public function isAdminOfOrganization(Organization $organization): bool
    {
        return in_array($this->roleInOrganization($organization), ['owner', 'admin'], true);
    }

    /**
     * Check if user can manage organization (owner or admin)
     */
    public function canManageOrganization(Organization $organization): bool
    {
        return $this->isAdminOfOrganization($organization);
    }

    /**
     * Get first organization (convenience method)
     */
    public function firstOrganization(): ?Organization
    {
        return $this->organizations()->first();
    }
}
