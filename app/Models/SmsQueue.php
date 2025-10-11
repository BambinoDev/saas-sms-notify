<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsQueue extends Model
{
    protected $table = 'sms_queue';

    protected $fillable = [
        'woman_id',
        // 'rule_id', // ← COMMENTÉ car n'existe pas dans la table
        'recipient_phone',
        'message_content',
        'sms_type',
        'scheduled_date',
        'scheduled_at',
        'status',
        'external_id',      // ← Africa's Talking Message ID
        'cost',             // ← SMS cost in FCFA
        'sent_at',
        'delivered_at',
        'retry_count',
        'error_message',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * Relationship with Case
     */
    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'woman_id');
    }

    /**
     * Relationship with Rule (COMMENTED - rule_id doesn't exist)
     */
    // public function rule()
    // {
    //     return $this->belongsTo(SmsRule::class);
    // }

    /**
     * Scope for pending SMS
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for sent SMS
     */
    public function scopeSent($query)
    {
        return $query->whereIn('status', ['sent', 'delivered']);
    }

    /**
     * Scope for failed SMS
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
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