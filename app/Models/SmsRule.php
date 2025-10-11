<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsRule extends Model
{
    protected $table = 'sms_rules';

    protected $fillable = [
        'name',
        'type',
        'days_before',
        'sending_time',
        'template',
        'active',
        'priority',
        // window_start, window_end, window_enabled - Pas encore migrées
    ];

    protected $casts = [
        'active' => 'boolean',
        'priority' => 'integer',
        'days_before' => 'integer',
    ];

    /**
     * Get available template variables
     */
    public static function getTemplateVariables()
    {
        return [
            'case_name' => 'Nom du case',
            'case_id' => 'ID CommCare du case',
            'visit_date' => 'Date du prochain rendez-vous',
            'visit_time' => 'Heure du rendez-vous',
            'facility_name' => 'Nom de la structure sanitaire',
            'district' => 'District sanitaire',
            'contact_phone' => 'Numéro de téléphone',
        ];
    }

    /**
     * Extract variables from template
     */
    public function getTemplateVariablesUsedAttribute()
    {
        preg_match_all('/\{([^}]+)\}/', $this->template, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Get character count
     */
    public function getCharacterCountAttribute()
    {
        return strlen($this->template ?? '');
    }

    /**
     * Relationship with SMS Queue (COMMENTED - rule_id doesn't exist yet)
     */
    // public function smsQueue()
    // {
    //     return $this->hasMany(SmsQueue::class, 'rule_id');
    // }

    /**
     * Scope for active rules
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Get sent count (SIMPLIFIED - without rule_id)
     */
    public function getSentCountAttribute()
    {
        // Return 0 for now, will implement when rule_id exists
        return 0;
    }

    /**
     * Get usage count (alias for sent_count)
     */
    public function getUsageCountAttribute()
    {
        return $this->sent_count;
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