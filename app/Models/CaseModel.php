<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CaseModel extends Model
{
    /**
     * The table associated with the model.
     * Using 'women' for now, will be renamed to 'cases' later
     */
    protected $table = 'women';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'case_id',
        'case_name',
        'contact_phone_number',
        'next_visit_date',
        'server_modified_on',
        'structure_sanitaire',
        'district_sanitaire',
        'region_sanitaire',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'next_visit_date' => 'date',
        'server_modified_on' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope for eligible cases (with valid phone)
     */
    public function scopeEligible($query)
    {
        return $query->whereNotNull('contact_phone_number')
                    ->where('contact_phone_number', '!=', '');
    }

    /**
     * Scope for active cases (updated recently)
     */
    public function scopeActive($query)
    {
        return $query->where('updated_at', '>=', Carbon::now()->subDays(30));
    }

    /**
     * Get case type (hardcoded for now, will be dynamic later)
     */
    public function getCaseTypeAttribute()
    {
        return 'Patient';
    }

    /**
     * Get status (active if updated recently)
     */
    public function getStatusAttribute()
    {
        $daysSinceUpdate = Carbon::parse($this->updated_at)->diffInDays(Carbon::now());
        return $daysSinceUpdate <= 30 ? 'active' : 'inactive';
    }

    /**
     * Get formatted phone
     */
    public function getFormattedPhoneAttribute()
    {
        return $this->contact_phone_number;
    }

    /**
     * Get last sync time (human readable)
     */
    public function getLastSyncAttribute()
    {
        return $this->updated_at ? $this->updated_at->diffForHumans() : 'Never';
    }

    /**
     * Get owner name (from structure_sanitaire)
     */
    public function getOwnerAttribute()
    {
        return $this->structure_sanitaire ?? 'N/A';
    }

    /**
     * Get owner name alias
     */
    public function getOwnerNameAttribute()
    {
        return $this->structure_sanitaire ?? 'N/A';
    }

    /**
     * Get closed status (default false for now, can be based on logic later)
     */
    public function getClosedAttribute()
    {
        // For now, consider a case closed if not updated in 90 days
        $daysSinceUpdate = Carbon::parse($this->updated_at)->diffInDays(Carbon::now());
        return $daysSinceUpdate > 90;
    }

    /**
     * Get facility name
     */
    public function getFacilityNameAttribute()
    {
        return $this->structure_sanitaire;
    }

    /**
     * Get district name
     */
    public function getDistrictAttribute()
    {
        return $this->district_sanitaire;
    }

    /**
     * Get region name
     */
    public function getRegionAttribute()
    {
        return $this->region_sanitaire;
    }

    /**
     * Relationship with SMS Queue
     */
    public function smsQueue()
    {
        return $this->hasMany(SmsQueue::class, 'woman_id');
    }

    /**
     * Relationship with Organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Scope: Filter by organization
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
}
