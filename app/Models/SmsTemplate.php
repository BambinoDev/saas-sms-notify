<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'case_type',
        'trigger_type',
        'days_before',
        'sending_time',
        'window_start',
        'window_end',
        'target_sequence_number',
        'message_template',
        'available_variables',
        'is_active',
        'priority',
        'conditions',
        'total_sent',
        'total_delivered',
        'total_failed',
    ];

    protected $casts = [
        'available_variables' => 'array',
        'is_active' => 'boolean',
        'conditions' => 'array',
    ];

    // Relations
    public function smsQueue(): HasMany
    {
        return $this->hasMany(SmsQueue::class, 'sms_template_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCaseType($query, $type)
    {
        return $query->where(function($q) use ($type) {
            $q->where('case_type', $type)
              ->orWhereNull('case_type');
        });
    }

    public function scopeDateBased($query)
    {
        return $query->where('trigger_type', 'date_based');
    }

    public function scopeByDaysBefore($query, $days)
    {
        return $query->where('days_before', $days);
    }

    // Methods
    public function renderMessage(array $data): string
    {
        $message = $this->message_template;
        
        // Mapping des variables standard
        $variables = [
            '{name}' => (string) ($data['case_name'] ?? $data['name'] ?? ''),
            '{case_name}' => (string) ($data['case_name'] ?? $data['name'] ?? ''),
            '{date}' => (string) ($data['primary_date'] ?? $data['date'] ?? ''),
            '{primary_date}' => (string) ($data['primary_date'] ?? $data['date'] ?? ''),
            '{time}' => (string) ($data['time'] ?? ''),
            '{location}' => (string) ($data['location'] ?? $data['location_name'] ?? ''),
            '{sequence_number}' => (string) ($data['sequence_number'] ?? ''),
        ];
        
        // Remplacer d'abord les variables avec ancienne syntaxe {{variable}}
        $oldSyntaxVariables = [
            '{{name}}' => $variables['{name}'],
            '{{case_name}}' => $variables['{case_name}'],
            '{{date}}' => $variables['{date}'],
            '{{primary_date}}' => $variables['{primary_date}'],
            '{{time}}' => $variables['{time}'],
            '{{location}}' => $variables['{location}'],
            '{{sequence_number}}' => $variables['{sequence_number}'],
        ];
        
        foreach ($oldSyntaxVariables as $placeholder => $value) {
            $message = str_replace($placeholder, $value, $message);
        }
        
        // Puis remplacer les variables avec nouvelle syntaxe {variable}
        foreach ($variables as $placeholder => $value) {
            $message = str_replace($placeholder, $value, $message);
        }
        
        return $message;
    }

    public function getSuccessRateAttribute(): float
    {
        if ($this->total_sent === 0) {
            return 0.0;
        }
        
        return round(($this->total_delivered / $this->total_sent) * 100, 2);
    }

    public function isApplicableFor($case): bool
    {
        // Vérifier si le template s'applique à ce cas
        if ($this->case_type && $this->case_type !== $case->case_type) {
            return false;
        }

        // Vérifier les conditions supplémentaires
        if ($this->conditions) {
            // Implémenter la logique de vérification des conditions
            return true;
        }

        return true;
    }
}
