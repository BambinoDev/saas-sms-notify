<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = tenant();
        
        if (!$tenant) {
            $this->command->error('No tenant context found');
            return;
        }

        $this->command->info("Seeding data for tenant: {$tenant->name}");

        // Déterminer le type de données basé sur l'industrie
        $industry = $tenant->industry;
        
        switch ($industry) {
            case 'health':
                $this->seedHealthData();
                break;
            case 'agriculture':
                $this->seedAgricultureData();
                break;
            case 'education':
                $this->seedEducationData();
                break;
            default:
                $this->seedGenericData();
        }

        // Créer les templates SMS pour tous les tenants
        $this->createSmsTemplates($industry);
        
        // Créer la queue SMS
        $this->createSmsQueue($industry);
    }

    /**
     * Seed health data (Hôpital Cocody)
     */
    private function seedHealthData(): void
    {
        $this->command->info('Creating health cases (women)...');

        $names = [
            'Fatou Traoré', 'Aminata Diabaté', 'Kadiatou Coulibaly', 'Mariam Koné', 'Aïcha Touré',
            'Oumou Sanogo', 'Fanta Keita', 'Ramatou Bamba', 'Maimouna Ouattara', 'Salimata Diallo',
            'Rokiatou Cissé', 'Djènèba Sangaré', 'Mariama Doumbia', 'Fatoumata Kanté', 'Aminata Soumahoro',
            'Kadiatou Fofana', 'Mariam Coulibaly', 'Aïcha Diarra', 'Oumou Traoré', 'Fanta Koné',
            'Ramatou Diabaté', 'Maimouna Sanogo', 'Salimata Keita', 'Rokiatou Bamba', 'Djènèba Ouattara',
            'Mariama Diallo', 'Fatoumata Cissé', 'Aminata Sangaré', 'Kadiatou Doumbia', 'Mariam Kanté',
            'Aïcha Soumahoro', 'Oumou Fofana', 'Fanta Coulibaly', 'Ramatou Diarra', 'Maimouna Traoré',
            'Salimata Koné', 'Rokiatou Diabaté', 'Djènèba Sanogo', 'Mariama Keita', 'Fatoumata Bamba',
            'Aminata Ouattara', 'Kadiatou Diallo', 'Mariam Cissé', 'Aïcha Sangaré', 'Oumou Doumbia',
            'Fanta Kanté', 'Ramatou Soumahoro', 'Maimouna Fofana', 'Salimata Coulibaly', 'Rokiatou Diarra'
        ];

        $bloodTypes = ['O+', 'A+', 'B+', 'AB+', 'O-', 'A-', 'B-', 'AB-'];

        foreach ($names as $index => $name) {
            $gestationalAge = rand(20, 38);
            $primaryDate = now()->addDays(rand(1, 30));
            $sequenceNumber = rand(1, 9);
            
            DB::table('cases')->insert([
                'case_id' => 'CHC-' . str_pad((string)($index + 1), 4, '0', STR_PAD_LEFT),
                'case_type' => 'woman',
                'case_name' => $name,
                'external_id' => 'commcare_' . Str::random(10),
                'contact_phone_number' => $this->generatePhoneNumber('CI'),
                'formatted_phone_number' => $this->generatePhoneNumber('CI'),
                'phone_valid' => true,
                'primary_date' => $primaryDate,
                'sequence_number' => $sequenceNumber,
                'owner_id' => 'chw_' . rand(1, 10),
                'owner_name' => 'CHW ' . rand(1, 10),
                'location_id' => 'loc_' . rand(1, 5),
                'location_name' => 'Quartier ' . ['Cocody', 'Abobo', 'Adjamé', 'Plateau', 'Yopougon'][rand(0, 4)],
                'case_opened_at' => now()->subDays(rand(1, 90)),
                'is_closed' => rand(0, 1) === 1,
                'is_eligible_for_sms' => true,
                'properties' => json_encode([
                    'gestational_age' => $gestationalAge,
                    'blood_type' => $bloodTypes[array_rand($bloodTypes)],
                    'risk_level' => rand(1, 3),
                    'last_visit' => now()->subDays(rand(1, 14)),
                ]),
                'last_synced_at' => now()->subHours(rand(1, 12)),
                'sync_batch_id' => 'batch_' . now()->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Seed agriculture data (Coopérative AgriMali)
     */
    private function seedAgricultureData(): void
    {
        $this->command->info('Creating agriculture cases (farmers)...');

        $names = [
            'Moussa Traoré', 'Amadou Coulibaly', 'Sékou Diabaté', 'Boubacar Koné', 'Modibo Sangaré',
            'Ibrahim Keita', 'Mamadou Bamba', 'Oumar Ouattara', 'Yaya Diallo', 'Bakary Cissé',
            'Alassane Doumbia', 'Cheick Kanté', 'Mamadou Soumahoro', 'Ibrahim Fofana', 'Boubacar Diarra',
            'Sékou Traoré', 'Amadou Koné', 'Moussa Diabaté', 'Modibo Sanogo', 'Oumar Keita',
            'Yaya Bamba', 'Bakary Ouattara', 'Alassane Diallo', 'Cheick Cissé', 'Mamadou Doumbia',
            'Ibrahim Kanté', 'Boubacar Soumahoro', 'Sékou Fofana', 'Amadou Coulibaly', 'Moussa Diarra'
        ];

        $cropTypes = ['maize', 'rice', 'cotton', 'millet', 'sorghum', 'peanuts'];

        foreach ($names as $index => $name) {
            $primaryDate = now()->addDays(rand(1, 60));
            $sequenceNumber = rand(1, 5);
            $hectares = rand(2, 20);
            
            DB::table('cases')->insert([
                'case_id' => 'AGM-' . str_pad((string)($index + 1), 4, '0', STR_PAD_LEFT),
                'case_type' => 'farmer',
                'case_name' => $name,
                'external_id' => 'commcare_' . Str::random(10),
                'contact_phone_number' => $this->generatePhoneNumber('ML'),
                'formatted_phone_number' => $this->generatePhoneNumber('ML'),
                'phone_valid' => true,
                'primary_date' => $primaryDate,
                'sequence_number' => $sequenceNumber,
                'owner_id' => 'extension_' . rand(1, 5),
                'owner_name' => 'Extension Worker ' . rand(1, 5),
                'location_id' => 'village_' . rand(1, 8),
                'location_name' => 'Village ' . ['Sikasso', 'Ségou', 'Mopti', 'Kayes', 'Koutiala', 'San', 'Djenné', 'Tombouctou'][rand(0, 7)],
                'case_opened_at' => now()->subDays(rand(1, 120)),
                'is_closed' => rand(0, 1) === 1,
                'is_eligible_for_sms' => true,
                'properties' => json_encode([
                    'crop_type' => $cropTypes[array_rand($cropTypes)],
                    'hectares' => $hectares,
                    'cooperative_member' => rand(0, 1) === 1,
                    'training_level' => rand(1, 3),
                    'last_visit' => now()->subDays(rand(1, 30)),
                ]),
                'last_synced_at' => now()->subHours(rand(1, 24)),
                'sync_batch_id' => 'batch_' . now()->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Seed education data (International School Accra)
     */
    private function seedEducationData(): void
    {
        $this->command->info('Creating education cases (students)...');

        $names = [
            'Kwame Asante', 'Akosua Mensah', 'Kofi Boateng', 'Ama Osei', 'Yaw Appiah',
            'Efua Owusu', 'Kojo Agyeman', 'Adwoa Tetteh', 'Nana Kwaku', 'Abena Danso',
            'Kweku Adjei', 'Serwaa Bonsu', 'Kwabena Ofori', 'Akua Asiedu', 'Yaw Mensah',
            'Efia Boateng', 'Kojo Osei', 'Adwoa Appiah', 'Nana Owusu', 'Abena Agyeman',
            'Kweku Tetteh', 'Serwaa Kwaku', 'Kwabena Danso', 'Akua Adjei', 'Yaw Bonsu',
            'Efia Ofori', 'Kojo Asiedu', 'Adwoa Mensah', 'Nana Boateng', 'Abena Osei',
            'Kweku Appiah', 'Serwaa Owusu', 'Kwabena Agyeman', 'Akua Tetteh', 'Yaw Kwaku',
            'Efia Danso', 'Kojo Adjei', 'Adwoa Bonsu', 'Nana Ofori', 'Abena Asiedu'
        ];

        $subjects = ['math', 'english', 'science', 'history', 'geography', 'french'];
        $grades = ['10', '11', '12'];

        foreach ($names as $index => $name) {
            $primaryDate = now()->addDays(rand(1, 45));
            $sequenceNumber = rand(1, 3);
            
            DB::table('cases')->insert([
                'case_id' => 'ISA-' . str_pad((string)($index + 1), 4, '0', STR_PAD_LEFT),
                'case_type' => 'student',
                'case_name' => $name,
                'external_id' => 'commcare_' . Str::random(10),
                'contact_phone_number' => $this->generatePhoneNumber('GH'),
                'formatted_phone_number' => $this->generatePhoneNumber('GH'),
                'phone_valid' => true,
                'primary_date' => $primaryDate,
                'sequence_number' => $sequenceNumber,
                'owner_id' => 'teacher_' . rand(1, 8),
                'owner_name' => 'Teacher ' . rand(1, 8),
                'location_id' => 'class_' . rand(1, 6),
                'location_name' => 'Class ' . ['A', 'B', 'C', 'D', 'E', 'F'][rand(0, 5)],
                'case_opened_at' => now()->subDays(rand(1, 180)),
                'is_closed' => rand(0, 1) === 1,
                'is_eligible_for_sms' => true,
                'properties' => json_encode([
                    'grade' => $grades[array_rand($grades)],
                    'subject' => $subjects[array_rand($subjects)],
                    'exam_type' => ['midterm', 'final', 'quiz'][rand(0, 2)],
                    'attendance_rate' => rand(70, 100),
                    'last_visit' => now()->subDays(rand(1, 7)),
                ]),
                'last_synced_at' => now()->subHours(rand(1, 6)),
                'sync_batch_id' => 'batch_' . now()->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Seed generic data (fallback)
     */
    private function seedGenericData(): void
    {
        $this->command->info('Creating generic cases...');

        for ($i = 1; $i <= 20; $i++) {
            DB::table('cases')->insert([
                'case_id' => 'GEN-' . str_pad((string)$i, 4, '0', STR_PAD_LEFT),
                'case_type' => 'case',
                'case_name' => 'Case ' . $i,
                'external_id' => 'commcare_' . Str::random(10),
                'contact_phone_number' => '+225' . rand(10000000, 99999999),
                'formatted_phone_number' => '+225' . rand(10000000, 99999999),
                'phone_valid' => true,
                'primary_date' => now()->addDays(rand(1, 30)),
                'sequence_number' => rand(1, 5),
                'owner_id' => 'owner_' . rand(1, 3),
                'owner_name' => 'Owner ' . rand(1, 3),
                'location_id' => 'loc_' . rand(1, 3),
                'location_name' => 'Location ' . rand(1, 3),
                'case_opened_at' => now()->subDays(rand(1, 60)),
                'is_closed' => rand(0, 1) === 1,
                'is_eligible_for_sms' => true,
                'properties' => json_encode(['generic' => true]),
                'last_synced_at' => now()->subHours(rand(1, 12)),
                'sync_batch_id' => 'batch_' . now()->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Create SMS templates
     */
    private function createSmsTemplates(string $industry): void
    {
        $this->command->info('Creating SMS templates...');

        $templates = [
            [
                'name' => 'Rappel J-7',
                'description' => 'Rappel 7 jours avant la date principale',
                'trigger_type' => 'date_based',
                'days_before' => 7,
                'sending_time' => '09:00:00',
                'message_template' => $this->getTemplateMessage($industry, 'J-7'),
                'priority' => 1,
            ],
            [
                'name' => 'Rappel J-2',
                'description' => 'Rappel 2 jours avant la date principale',
                'trigger_type' => 'date_based',
                'days_before' => 2,
                'sending_time' => '10:00:00',
                'message_template' => $this->getTemplateMessage($industry, 'J-2'),
                'priority' => 2,
            ],
            [
                'name' => 'Rappel J-1',
                'description' => 'Rappel 1 jour avant la date principale',
                'trigger_type' => 'date_based',
                'days_before' => 1,
                'sending_time' => '08:00:00',
                'message_template' => $this->getTemplateMessage($industry, 'J-1'),
                'priority' => 3,
            ],
        ];

        foreach ($templates as $template) {
            DB::table('sms_templates')->insert([
                'name' => $template['name'],
                'description' => $template['description'],
                'case_type' => $this->getDefaultCaseType($industry),
                'trigger_type' => $template['trigger_type'],
                'days_before' => $template['days_before'],
                'sending_time' => $template['sending_time'],
                'message_template' => $template['message_template'],
                'available_variables' => json_encode(['{name}', '{date}', '{time}', '{location}']),
                'is_active' => true,
                'priority' => $template['priority'],
                'conditions' => json_encode([]),
                'total_sent' => rand(10, 100),
                'total_delivered' => rand(8, 95),
                'total_failed' => rand(0, 5),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Create SMS queue entries
     */
    private function createSmsQueue(string $industry): void
    {
        $this->command->info('Creating SMS queue entries...');

        $cases = DB::table('cases')->where('is_eligible_for_sms', true)->take(15)->get();
        $templates = DB::table('sms_templates')->get();

        if ($cases->isEmpty() || $templates->isEmpty()) {
            return;
        }

        $statuses = ['pending', 'sent', 'delivered', 'failed'];
        $gateways = ['twilio', 'africastalking', 'orange'];

        foreach ($cases as $case) {
            $template = $templates->random();
            $status = $statuses[array_rand($statuses)];
            $scheduledAt = now()->subDays(rand(0, 7));
            
            $smsQueueId = DB::table('sms_queue')->insertGetId([
                'case_id' => $case->id,
                'sms_template_id' => $template->id,
                'phone_number' => $case->contact_phone_number,
                'message' => str_replace(['{name}', '{date}'], [$case->case_name, $case->primary_date], $template->message_template),
                'message_length' => strlen(str_replace(['{name}', '{date}'], [$case->case_name, $case->primary_date], $template->message_template)),
                'sms_parts' => 1,
                'scheduled_at' => $scheduledAt,
                'status' => $status,
                'sent_at' => $status !== 'pending' ? $scheduledAt->addMinutes(rand(1, 30)) : null,
                'delivered_at' => $status === 'delivered' ? $scheduledAt->addMinutes(rand(5, 60)) : null,
                'failed_at' => $status === 'failed' ? $scheduledAt->addMinutes(rand(1, 10)) : null,
                'gateway_provider' => $gateways[array_rand($gateways)],
                'gateway_message_id' => 'msg_' . Str::random(20),
                'gateway_response' => $status === 'failed' ? 'Invalid phone number' : 'Success',
                'error_code' => $status === 'failed' ? 'INVALID_PHONE' : null,
                'error_message' => $status === 'failed' ? 'Phone number not valid' : null,
                'retry_count' => $status === 'failed' ? rand(1, 3) : 0,
                'cost_cents' => rand(5, 15),
                'metadata' => json_encode(['industry' => $industry]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Créer un rapport de livraison si le SMS a été envoyé
            if (in_array($status, ['sent', 'delivered', 'failed'])) {
                DB::table('sms_delivery_reports')->insert([
                    'sms_queue_id' => $smsQueueId,
                    'gateway_message_id' => 'msg_' . Str::random(20),
                    'gateway_status' => $status,
                    'gateway_raw_payload' => json_encode(['status' => $status, 'timestamp' => now()]),
                    'reported_at' => now()->subHours(rand(1, 24)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Generate phone number for country
     */
    private function generatePhoneNumber(string $countryIso): string
    {
        return match ($countryIso) {
            'CI' => '+225' . rand(10000000, 99999999),
            'ML' => '+223' . rand(10000000, 99999999),
            'GH' => '+233' . rand(10000000, 99999999),
            default => '+225' . rand(10000000, 99999999),
        };
    }

    /**
     * Get template message based on industry and timing
     */
    private function getTemplateMessage(string $industry, string $timing): string
    {
        return match ([$industry, $timing]) {
            ['health', 'J-7'] => 'Bonjour {name}, votre rendez-vous de suivi prénatal est prévu le {date}. Centre Hospitalier Cocody.',
            ['health', 'J-2'] => 'Rappel: RDV prénatal {name} le {date}. Appelez-nous si changement: +225 20 30 40 50',
            ['health', 'J-1'] => 'URGENT {name}: Votre RDV prénatal est DEMAIN {date}. Centre Hospitalier Cocody.',
            ['agriculture', 'J-7'] => 'Bonjour {name}, formation agricole prévue le {date}. Coopérative AgriMali.',
            ['agriculture', 'J-2'] => 'Rappel formation {name} le {date}. Techniques de culture modernes. AgriMali.',
            ['agriculture', 'J-1'] => 'Dernière chance {name}: Formation demain {date}. Coopérative AgriMali.',
            ['education', 'J-7'] => 'Bonjour {name}, examen prévu le {date}. International School Accra.',
            ['education', 'J-2'] => 'Rappel examen {name} le {date}. Préparez-vous bien! ISA.',
            ['education', 'J-1'] => 'IMPORTANT {name}: Examen DEMAIN {date}. Bonne chance! ISA.',
            default => 'Rappel {name}: Rendez-vous le {date}.',
        };
    }

    /**
     * Get default case type for industry
     */
    private function getDefaultCaseType(string $industry): string
    {
        return match ($industry) {
            'health' => 'woman',
            'agriculture' => 'farmer',
            'education' => 'student',
            default => 'case',
        };
    }
}
