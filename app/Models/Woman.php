<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Woman extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'case_id',
        'case_name',
        'client_age',
        'contact_phone_number',
        'husband_phone_number',
        'contact_phone_number_is_verified',
        'consent_sms_yes',
        'anc_counter',
        'next_visit_date',
        'two_days_before_next_visit_date',
        'structure_sanitaire',
        'district_sanitaire',
        'region_sanitaire',
        'sms_reminder_counter',
        'raw_properties',
        'closed',
    ];

    protected $casts = [
        'contact_phone_number_is_verified' => 'boolean',
        'consent_sms_yes' => 'boolean',
        'closed' => 'boolean',
        'next_visit_date' => 'date',
        'two_days_before_next_visit_date' => 'date',
        'raw_properties' => 'array',
        'client_age' => 'integer',
        'anc_counter' => 'integer',
        'sms_reminder_counter' => 'integer',
    ];

    // Relations
    public function smsQueue()
    {
        return $this->hasMany(SmsQueue::class);
    }

    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class);
    }

    // Scopes pour filtrage
    public function scopeEligibleForSms($query)
    {
        return $query->where('consent_sms_yes', true)
                     ->where('contact_phone_number_is_verified', true)
                     ->where('closed', false)
                     ->where(function($q) {
                         $q->whereNotNull('contact_phone_number')
                           ->orWhereNotNull('husband_phone_number');
                     });
    }

    public function scopeHasAppointmentOn($query, $date)
    {
        return $query->whereDate('next_visit_date', $date);
    }

    public function scopeHasReminderOn($query, $date)
    {
        return $query->whereDate('two_days_before_next_visit_date', $date);
    }

    public function scopeActive($query)
    {
        return $query->where('closed', false);
    }

    // Accessors
    public function getPhoneNumberAttribute()
    {
        // Priorité : contact_phone_number, sinon husband_phone_number
        $phone = $this->contact_phone_number ?: $this->husband_phone_number;
        
        if (!$phone) {
            return null;
        }

        // Nettoyer le numéro (enlever espaces, tirets, etc.)
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Ajouter préfixe 225 (Côte d'Ivoire) si absent
        if (!str_starts_with($phone, '+225') && !str_starts_with($phone, '225')) {
            if (strlen($phone) === 10) {
                $phone = '225' . $phone;
            }
        }
        
        // Format final : 225XXXXXXXXXX
        return str_replace('+', '', $phone);
    }

    public function getNextAncNumberAttribute()
    {
        return $this->anc_counter + 1;
    }

    public function getFullNameAttribute()
    {
        return $this->case_name;
    }

    public function getIneligibilityReasonAttribute(): ?string
    {
        if ($this->canReceiveSms()) {
            return null; // Éligible
        }

        $reasons = [];

        if (!$this->consent_sms_yes) {
            $reasons[] = 'Pas de consentement';
        }

        if (!$this->contact_phone_number_is_verified) {
            $reasons[] = 'Téléphone non vérifié';
        }

        if (!$this->contact_phone_number && !$this->husband_phone_number) {
            $reasons[] = 'Aucun numéro de téléphone';
        }

        if ($this->closed) {
            $reasons[] = 'Dossier fermé';
        }

        return implode(', ', $reasons);
    }

    // Méthodes utilitaires
    public function hasAppointmentToday(): bool
    {
        return $this->next_visit_date?->isToday() ?? false;
    }

    public function hasReminderToday(): bool
    {
        return $this->two_days_before_next_visit_date?->isToday() ?? false;
    }

    public function canReceiveSms(): bool
    {
        return $this->consent_sms_yes 
            && $this->contact_phone_number_is_verified 
            && !$this->closed
            && $this->phone_number !== null;
    }

    public function incrementSmsCounter(): void
    {
        $this->increment('sms_reminder_counter');
    }
}