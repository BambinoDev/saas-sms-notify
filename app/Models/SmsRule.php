<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class SmsRule extends Model
{
    protected $fillable = [
        'organization_id',
        'sms_template_id',
        'name',
        'description',
        'is_active',
        'trigger_field',
        'trigger_condition',
        'trigger_value',
        'trigger_unit',
        'send_time',
        'total_sent',
        'total_failed',
        'last_executed_at',
        // NOUVELLES COLONNES
        'generation_frequency',
        'generation_time',
        'generation_day_of_week',
        'generation_day_of_month',
        'is_paused',
        'paused_at',
        'pause_reason',
        'total_generated',
        'total_delivered',
        'last_generated_at',
        'priority',
        'daily_limit',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_paused' => 'boolean',
        'send_time' => 'datetime:H:i',
        'last_executed_at' => 'datetime',
        'paused_at' => 'datetime',
        'last_generated_at' => 'datetime',
        'total_generated' => 'integer',
        'total_sent' => 'integer',
        'total_delivered' => 'integer',
        'total_failed' => 'integer',
        'priority' => 'integer',
        'daily_limit' => 'integer',
        'generation_day_of_week' => 'integer',
        'generation_day_of_month' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============================================
    // RELATIONS
    // ============================================

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(SmsTemplate::class, 'sms_template_id');
    }

    public function smsQueue(): HasMany
    {
        return $this->hasMany(SmsQueue::class, 'rule_id');
    }

    public function generationLogs(): HasMany
    {
        return $this->hasMany(SmsGenerationLog::class, 'rule_id');
    }

    // ============================================
    // SCOPES
    // ============================================

    /**
     * Règles actives et non pausées
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('is_paused', false);
    }

    /**
     * Règles qui doivent générer maintenant (automatique)
     */
    public function scopeWhereGenerationDue(Builder $query): Builder
    {
        $now = now();
        
        return $query->where('is_active', true)
            ->where('is_paused', false)
            ->where(function($q) use ($now) {
                // Daily : générer si on est passé l'heure de génération
                $q->where(function($q2) use ($now) {
                    $q2->where('generation_frequency', 'daily')
                        ->whereTime('generation_time', '<=', $now->format('H:i:s'));
                })
                // Weekly : bon jour + bonne heure
                ->orWhere(function($q2) use ($now) {
                    $q2->where('generation_frequency', 'weekly')
                        ->where('generation_day_of_week', $now->dayOfWeek)
                        ->whereTime('generation_time', '<=', $now->format('H:i:s'));
                })
                // Monthly : bon jour du mois + bonne heure
                ->orWhere(function($q2) use ($now) {
                    $q2->where('generation_frequency', 'monthly')
                        ->where('generation_day_of_month', $now->day)
                        ->whereTime('generation_time', '<=', $now->format('H:i:s'));
                });
            })
            // Ne pas regénérer si déjà fait aujourd'hui
            ->where(function($q) {
                $q->whereNull('last_generated_at')
                    ->orWhereDate('last_generated_at', '<', today());
            });
    }

    /**
     * Ordre par priorité
     */
    public function scopeOrderByPriority(Builder $query): Builder
    {
        return $query->orderBy('priority', 'asc');
    }

    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    // ============================================
    // METHODS - PAUSE/RESUME
    // ============================================

    /**
     * Mettre la règle en pause
     */
    public function pause(?string $reason = null): void
    {
        $this->update([
            'is_paused' => true,
            'paused_at' => now(),
            'pause_reason' => $reason,
        ]);
    }

    /**
     * Reprendre la règle
     */
    public function resume(): void
    {
        $this->update([
            'is_paused' => false,
            'paused_at' => null,
            'pause_reason' => null,
        ]);
    }

    /**
     * Vérifier si la règle peut générer
     */
    public function canGenerate(): bool
    {
        return $this->is_active && !$this->is_paused;
    }

    // ============================================
    // METHODS - STATISTIQUES
    // ============================================

    /**
     * Incrémenter les stats
     */
    public function incrementStats(int $generated = 0, int $sent = 0, int $delivered = 0, int $failed = 0): void
    {
        $this->increment('total_generated', $generated);
        $this->increment('total_sent', $sent);
        $this->increment('total_delivered', $delivered);
        $this->increment('total_failed', $failed);
        
        if ($generated > 0) {
            $this->update(['last_generated_at' => now()]);
        }
    }

    /**
     * Vérifier si limite quotidienne atteinte
     */
    public function hasDailyLimitReached(): bool
    {
        if (!$this->daily_limit) {
            return false; // Pas de limite
        }

        $todayGenerated = $this->smsQueue()
            ->whereDate('created_at', today())
            ->count();

        return $todayGenerated >= $this->daily_limit;
    }

    /**
     * Calculer le taux de succès
     */
    public function getSuccessRateAttribute(): float
    {
        $total = $this->total_sent + $this->total_failed;
        
        if ($total === 0) {
            return 0.0;
        }
        
        return round(($this->total_sent / $total) * 100, 1);
    }

    /**
     * Obtenir la prochaine date de génération
     */
    public function getNextGenerationTimeAttribute(): ?Carbon
    {
        if (!$this->canGenerate()) {
            return null;
        }

        $now = now();

        if ($this->generation_frequency === 'daily') {
            $next = $now->copy()->setTimeFromTimeString($this->generation_time);
            
            if ($next <= $now) {
                $next->addDay();
            }
            
            return $next;
        }

        if ($this->generation_frequency === 'weekly') {
            $targetDay = $this->generation_day_of_week;
            $currentDay = $now->dayOfWeek;
            
            $daysToAdd = ($targetDay - $currentDay + 7) % 7;
            
            if ($daysToAdd === 0) {
                $next = $now->copy()->setTimeFromTimeString($this->generation_time);
                if ($next <= $now) {
                    $daysToAdd = 7;
                }
            }
            
            return $now->copy()
                ->addDays($daysToAdd)
                ->setTimeFromTimeString($this->generation_time);
        }

        if ($this->generation_frequency === 'monthly') {
            $targetDay = $this->generation_day_of_month;
            $currentDay = $now->day;
            
            $next = $now->copy()
                ->setDay(min($targetDay, $now->daysInMonth))
                ->setTimeFromTimeString($this->generation_time);
            
            if ($currentDay > $targetDay || ($currentDay === $targetDay && $next <= $now)) {
                $next->addMonth()->setDay(min($targetDay, $next->daysInMonth));
            }
            
            return $next;
        }

        return null;
    }

    // ============================================
    // ACCESSORS (existant)
    // ============================================

    public function getFormattedTriggerAttribute(): string
    {
        $value = $this->trigger_value;
        $unit = $this->trigger_unit;
        $condition = $this->trigger_condition;

        return match($condition) {
            'before' => "{$value} {$unit} avant {$this->trigger_field}",
            'after' => "{$value} {$unit} après {$this->trigger_field}",
            'equals' => "Quand {$this->trigger_field} = {$value}",
            'between' => "Entre {$value} {$unit}",
            default => $condition,
        };
    }
}