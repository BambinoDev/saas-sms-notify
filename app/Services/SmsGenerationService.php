<?php

namespace App\Services;

use App\Models\SmsRule;
use App\Models\CaseModel;
use App\Models\SmsQueue;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SmsGenerationService
{
    /**
     * Generate SMS for a specific rule
     * 
     * @param SmsRule $rule
     * @return array Statistics (generated, skipped, errors)
     */
    public function generateForRule(SmsRule $rule)
    {
        $stats = [
            'generated' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        Log::info("Processing rule", [
            'rule_id' => $rule->id,
            'rule_name' => $rule->name,
            'type' => $rule->type,
            'days_before' => $rule->days_before,
        ]);

        try {
            // Calculate target date for this rule
            $targetDate = $this->calculateTargetDate($rule->days_before);

            Log::info("Target date calculated", [
                'days_before' => $rule->days_before,
                'target_date' => $targetDate->toDateString(),
            ]);

            // Find eligible cases
            $cases = $this->findEligibleCases($targetDate);

            Log::info("Eligible cases found", [
                'count' => $cases->count(),
            ]);

            // Generate SMS for each case
            foreach ($cases as $case) {
                try {
                    // Check if SMS already exists for this case + type + date
                    $exists = SmsQueue::where('woman_id', $case->id)
                        ->where('sms_type', $rule->type)
                        ->whereDate('scheduled_at', $targetDate)
                        ->exists();

                    if ($exists) {
                        $stats['skipped']++;
                        continue;
                    }

                    // Validate phone number
                    if (!$this->isValidPhone($case->contact_phone_number)) {
                        Log::warning("Invalid phone number", [
                            'case_id' => $case->case_id,
                            'phone' => $case->contact_phone_number,
                        ]);
                        $stats['errors']++;
                        continue;
                    }

                    // Generate message from template
                    $message = $this->generateMessage($rule->template, $case);

                    // Calculate scheduled time
                    $scheduledAt = $this->calculateScheduledTime($targetDate, $rule);

                    // Create SMS in queue
                    SmsQueue::create([
                        'woman_id' => $case->id,
                        'sms_type' => $rule->type,
                        'recipient_phone' => $case->contact_phone_number,
                        'message_content' => $message,
                        'scheduled_date' => $targetDate->toDateString(),
                        'scheduled_at' => $scheduledAt,
                        'status' => 'pending',
                    ]);

                    $stats['generated']++;

                } catch (\Exception $e) {
                    Log::error("Error generating SMS for case", [
                        'case_id' => $case->case_id ?? 'unknown',
                        'error' => $e->getMessage(),
                    ]);
                    $stats['errors']++;
                }
            }

        } catch (\Exception $e) {
            Log::error("Error processing rule", [
                'rule_id' => $rule->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        return $stats;
    }

    /**
     * Calculate target date based on days_before
     * 
     * @param int $daysBefore Positive = before, Negative = after
     * @return Carbon
     */
    private function calculateTargetDate($daysBefore)
    {
        // days_before = 2 → Target = today + 2 days (visit in 2 days)
        // days_before = -1 → Target = today - 1 day (visit was yesterday)
        return now()->addDays($daysBefore);
    }

    /**
     * Find cases eligible for SMS
     * 
     * @param Carbon $targetDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function findEligibleCases($targetDate)
    {
        return CaseModel::whereDate('next_visit_date', $targetDate)
            ->whereNotNull('contact_phone_number')
            ->where('contact_phone_number', '!=', '')
            ->get();
    }

    /**
     * Validate phone number (Côte d'Ivoire format)
     * 
     * @param string|null $phone
     * @return bool
     */
    private function isValidPhone($phone)
    {
        if (!$phone) {
            return false;
        }

        // Remove spaces for validation
        $phoneClean = str_replace(' ', '', $phone);

        // Check format: +225XXXXXXXXXX
        if (!preg_match('/^\+225[0-9]{10}$/', $phoneClean)) {
            return false;
        }

        // Check valid prefixes (01, 05, 07)
        $prefix = substr($phoneClean, 4, 2);
        return in_array($prefix, ['01', '05', '07']);
    }

    /**
     * Generate message from template with variable replacement
     * 
     * @param string $template
     * @param CaseModel $case
     * @return string
     */
    private function generateMessage($template, $case)
    {
        $variables = [
            'case_name' => $case->case_name,
            'case_id' => $case->case_id,
            'visit_date' => $case->next_visit_date ? $case->next_visit_date->format('d/m/Y') : 'N/A',
            'visit_time' => $case->next_visit_date ? $case->next_visit_date->format('H:i') : 'N/A',
            'facility_name' => $case->structure_sanitaire ?? 'votre centre de santé',
            'district' => $case->district_sanitaire ?? 'N/A',
            'contact_phone' => $case->contact_phone_number,
        ];

        // Replace all variables in template
        $message = $template;
        foreach ($variables as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }

        return $message;
    }

    /**
     * Calculate scheduled time based on rule's sending_time
     * 
     * IMPORTANT: scheduled_at = QUAND envoyer le SMS (aujourd'hui!)
     *            $date = Date du RDV (utilisé seulement pour scheduled_date)
     * 
     * @param Carbon $date Date du RDV (pour scheduled_date uniquement)
     * @param SmsRule $rule Règle contenant sending_time
     * @return Carbon Quand envoyer le SMS (aujourd'hui à sending_time)
     */
    private function calculateScheduledTime($date, $rule)
    {
        // Parse rule sending time
        $sendingTime = Carbon::createFromTimeString($rule->sending_time);

        // Combine TODAY with sending time (not $date which is the appointment date)
        $scheduledAt = now()
            ->setHour($sendingTime->hour)
            ->setMinute($sendingTime->minute)
            ->setSecond(0);

        // Si l'heure est déjà passée aujourd'hui, envoyer immédiatement
        if ($scheduledAt->isPast()) {
            $scheduledAt = now();
        }

        return $scheduledAt;
    }
}
