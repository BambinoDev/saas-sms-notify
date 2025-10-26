<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SmsRule;
use App\Models\SmsQueue;
use App\Models\CaseModel;
use App\Models\SmsGenerationLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SmsGenerationService
{
    /**
     * Générer les SMS pour une règle donnée
     * 
     * @param SmsRule $rule
     * @param string $triggerType 'automatic' ou 'manual'
     * @param int|null $userId User ID si manuel
     */
    public function generateForRule(SmsRule $rule, string $triggerType = 'manual', ?int $userId = null): array
    {
        $startTime = now();
        
        // Créer le log de génération
        $log = SmsGenerationLog::create([
            'organization_id' => $rule->organization_id,
            'rule_id' => $rule->id,
            'user_id' => $userId ?? Auth::id(),
            'trigger_type' => $triggerType,
            'started_at' => $startTime,
            'status' => 'running',
        ]);

        try {
            // Vérifier que la règle peut générer
            if (!$rule->canGenerate()) {
                throw new \Exception('Rule cannot generate SMS (inactive or paused)');
            }

            // Vérifier limite quotidienne
            if ($rule->hasDailyLimitReached()) {
                throw new \Exception('Daily SMS limit reached for this rule');
            }

            // Récupérer les cases éligibles
            $eligibleCases = $this->getEligibleCases($rule);
            
            $generated = 0;
            $duplicates = 0;
            $errors = 0;

            foreach ($eligibleCases as $case) {
                try {
                    // Vérifier doublons (même case + règle + aujourd'hui)
                    if ($this->isDuplicate($rule, $case)) {
                        $duplicates++;
                        continue;
                    }

                    // Générer le SMS
                    $this->generateSmsForCase($rule, $case);
                    $generated++;

                    // Vérifier limite en temps réel
                    if ($rule->hasDailyLimitReached()) {
                        Log::warning('Daily limit reached during generation', [
                            'rule_id' => $rule->id,
                            'generated' => $generated,
                        ]);
                        break;
                    }

                } catch (\Exception $e) {
                    $errors++;
                    Log::error('Error generating SMS for case', [
                        'rule_id' => $rule->id,
                        'case_id' => $case->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Mettre à jour les stats de la règle
            $rule->incrementStats($generated);

            // Finaliser le log
            $completedAt = now();
            $log->update([
                'completed_at' => $completedAt,
                'duration_seconds' => (int) $completedAt->diffInSeconds($startTime),
                'status' => $errors > 0 ? 'partial' : 'success',
                'total_cases_evaluated' => $eligibleCases->count(),
                'total_sms_generated' => $generated,
                'total_duplicates_skipped' => $duplicates,
                'total_errors' => $errors,
            ]);

            Log::info('SMS generation completed', [
                'rule_id' => $rule->id,
                'rule_name' => $rule->name,
                'trigger_type' => $triggerType,
                'evaluated' => $eligibleCases->count(),
                'generated' => $generated,
                'duplicates' => $duplicates,
                'errors' => $errors,
                'duration' => $completedAt->diffInSeconds($startTime) . 's',
            ]);

            return [
                'success' => true,
                'generated' => $generated,
                'duplicates' => $duplicates,
                'errors' => $errors,
                'total_evaluated' => $eligibleCases->count(),
            ];

        } catch (\Exception $e) {
            $log->update([
                'completed_at' => now(),
                'duration_seconds' => (int) now()->diffInSeconds($startTime),
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);

            Log::error('SMS generation failed', [
                'rule_id' => $rule->id,
                'trigger_type' => $triggerType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'generated' => 0,
                'duplicates' => 0,
                'errors' => 1,
            ];
        }
    }

    /**
     * Générer pour toutes les règles dues (appelé par Job)
     */
    public function generateForAllDueRules(): array
    {
        $rules = SmsRule::active()
            ->whereGenerationDue()
            ->orderByPriority()
            ->get();

        $results = [
            'total_rules' => $rules->count(),
            'total_generated' => 0,
            'total_duplicates' => 0,
            'total_errors' => 0,
            'rules_processed' => [],
        ];

        foreach ($rules as $rule) {
            $result = $this->generateForRule($rule, 'automatic');
            
            $results['total_generated'] += $result['generated'];
            $results['total_duplicates'] += $result['duplicates'];
            $results['total_errors'] += $result['errors'];
            
            $results['rules_processed'][] = [
                'rule_id' => $rule->id,
                'rule_name' => $rule->name,
                'generated' => $result['generated'],
                'success' => $result['success'],
            ];
        }

        Log::info('Automatic SMS generation completed', $results);

        return $results;
    }

    /**
     * Récupérer les cases éligibles pour une règle
     * 
     * ✅ CORRECTION : Ne pas utiliser "status" ou "closed" (ce sont des accessors)
     * ✅ Filtrer par updated_at pour avoir les cases actifs
     */
    private function getEligibleCases(SmsRule $rule)
    {
        // ✅ Cases actifs = mis à jour dans les 90 derniers jours
        $query = CaseModel::where('organization_id', $rule->organization_id)
            ->where('updated_at', '>=', now()->subDays(90))
            ->whereNotNull('contact_phone_number')
            ->where('contact_phone_number', '!=', '');

        // Appliquer les conditions de la règle
        $field = $rule->trigger_field;
        $condition = $rule->trigger_condition;
        $value = $rule->trigger_value;

        // Calculer la date cible selon la condition
        if ($condition === 'before') {
            // ✅ CORRECTION : Si le champ est déjà "J-X avant RDV", comparer à aujourd'hui
            // Ex: two_days_before_next_visit_date = aujourd'hui signifie que le RDV est dans 2 jours
            if (str_contains($field, 'before')) {
                // Le champ est déjà la date "J-X avant", on cherche celles égales à aujourd'hui
                $query->whereDate($field, '=', today());
            } else {
                // Champ normal: next_visit_date = dans X jours
                $targetDate = now()->addDays((int)$value)->toDateString();
                $query->whereDate($field, '=', $targetDate);
            }
        } elseif ($condition === 'after') {
            // Ex: created_at = il y a 7 jours
            $targetDate = now()->subDays((int)$value)->toDateString();
            $query->whereDate($field, '=', $targetDate);
        } elseif ($condition === 'equals') {
            // Cas spécial : si value = '0' et field = 'next_visit_date', chercher aujourd'hui
            if ($value === '0' && $field === 'next_visit_date') {
                $query->whereDate($field, '=', today());
            } else {
                // Ex: field = 'value'
                $query->where($field, '=', $value);
            }
        }

        return $query->get();
    }

    /**
     * Vérifier si SMS déjà généré aujourd'hui pour ce case + règle
     * 
     * ✅ CORRECTION : Utiliser woman_id au lieu de case_id
     */
    private function isDuplicate(SmsRule $rule, CaseModel $case): bool
    {
        return SmsQueue::where('organization_id', $rule->organization_id)
            ->where('rule_id', $rule->id)
            ->where('woman_id', $case->id) // ✅ woman_id, pas case_id
            ->whereDate('created_at', today())
            ->exists();
    }

    /**
     * Générer un SMS pour un case donné
     * 
     * ✅ CORRECTION : Utiliser les bons noms de colonnes SmsQueue
     * ✅ CORRECTION : Respecter l'heure d'envoi configurée dans la règle
     */
    private function generateSmsForCase(SmsRule $rule, CaseModel $case): void
    {
        // Remplacer les variables dans le template
        $message = $this->replaceVariables($rule->template->content, $case);

        // Calculer l'heure d'envoi selon la règle
        $scheduledAt = $this->calculateScheduledTime($rule);

        // ✅ Créer le SMS avec les BONS noms de colonnes
        SmsQueue::create([
            'organization_id' => $rule->organization_id,
            'rule_id' => $rule->id,
            'woman_id' => $case->id,                    // ✅ woman_id
            'recipient_phone' => $case->contact_phone_number, // ✅ recipient_phone
            'message_content' => $message,              // ✅ message_content
            'sms_type' => $this->normalizeSmsType($rule->name), // ✅ sms_type normalisé
            'status' => 'pending',
            'scheduled_at' => $scheduledAt, // ✅ Respecter l'heure d'envoi de la règle
            'scheduled_date' => $scheduledAt->toDateString(),
        ]);
    }

    /**
     * Calculer l'heure d'envoi selon la règle
     * 
     * @param SmsRule $rule
     * @return Carbon
     */
    private function calculateScheduledTime(SmsRule $rule): Carbon
    {
        // Si la règle a une heure d'envoi configurée
        if ($rule->send_time) {
            $sendTime = Carbon::parse($rule->send_time);
            
            // Calculer la date d'envoi selon le type de déclencheur
            if ($rule->trigger_condition === 'before') {
                // Ex: J-2, envoyer à l'heure configurée dans 2 jours
                $targetDate = now()->addDays((int)$rule->trigger_value);
            } elseif ($rule->trigger_condition === 'after') {
                // Ex: après 7 jours, envoyer à l'heure configurée aujourd'hui
                $targetDate = now();
            } elseif ($rule->trigger_condition === 'equals' && $rule->trigger_value === '0') {
                // Jour J, envoyer à l'heure configurée aujourd'hui
                $targetDate = now();
            } else {
                // Par défaut, envoyer aujourd'hui à l'heure configurée
                $targetDate = now();
            }
            
            // Combiner la date cible avec l'heure d'envoi
            $scheduledAt = $targetDate->copy()
                ->setTime($sendTime->hour, $sendTime->minute, 0);
            
            // Si l'heure est déjà passée aujourd'hui, programmer pour demain
            if ($scheduledAt->isPast()) {
                $scheduledAt->addDay();
            }
            
            return $scheduledAt;
        }
        
        // Si pas d'heure configurée, envoyer immédiatement
        return now();
    }

    /**
     * Normaliser le sms_type pour respecter la contrainte de la base de données
     * Format requis: ^[a-z0-9-]+$
     */
    private function normalizeSmsType(string $ruleName): string
    {
        return strtolower(
            preg_replace('/[^a-zA-Z0-9-]/', '-', $ruleName)
        );
    }

    /**
     * Remplacer les variables dans le message
     */
    private function replaceVariables(string $content, CaseModel $case): string
    {
        $variables = [
            '{case_name}' => $case->case_name ?? '',
            '{contact_phone_number}' => $case->contact_phone_number ?? '',
            '{next_visit_date}' => $case->next_visit_date ? 
                Carbon::parse($case->next_visit_date)->format('d/m/Y') : '',
            '{structure_sanitaire}' => $case->structure_sanitaire ?? '',
            '{district_sanitaire}' => $case->district_sanitaire ?? '',
            '{region_sanitaire}' => $case->region_sanitaire ?? '',
            '{date}' => now()->format('d/m/Y'),
            '{time}' => now()->format('H:i'),
        ];

        return str_replace(
            array_keys($variables),
            array_values($variables),
            $content
        );
    }
}