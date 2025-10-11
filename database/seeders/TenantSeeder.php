<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding tenants...');

        $tenants = [
            [
                'name' => 'Centre Hospitalier Cocody',
                'slug' => 'hopital-cocody',
                'industry' => 'health',
                'country_iso' => 'CI',
                'timezone' => 'Africa/Abidjan',
                'status' => 'active',
                'data' => [
                    'description' => 'Hôpital public de référence à Abidjan',
                    'contact_email' => 'contact@chcocody.ci',
                    'contact_phone' => '+225 20 30 40 50',
                ],
            ],
            [
                'name' => 'Coopérative AgriMali',
                'slug' => 'agri-mali',
                'industry' => 'agriculture',
                'country_iso' => 'ML',
                'timezone' => 'Africa/Bamako',
                'status' => 'trial',
                'data' => [
                    'description' => 'Coopérative agricole pour le développement rural',
                    'contact_email' => 'info@agrimali.ml',
                    'contact_phone' => '+223 20 22 33 44',
                ],
            ],
            [
                'name' => 'International School Accra',
                'slug' => 'school-accra',
                'industry' => 'education',
                'country_iso' => 'GH',
                'timezone' => 'Africa/Accra',
                'status' => 'active',
                'data' => [
                    'description' => 'École internationale bilingue à Accra',
                    'contact_email' => 'admin@schoolaccra.gh',
                    'contact_phone' => '+233 30 25 67 89',
                ],
            ],
        ];

        foreach ($tenants as $tenantData) {
            $this->command->info("Creating tenant: {$tenantData['name']}");

            // Créer le tenant (cela va automatiquement créer le schéma et migrer)
            $tenant = Tenant::create([
                'id' => Str::uuid(),
                'name' => $tenantData['name'],
                'slug' => $tenantData['slug'],
                'database_schema' => 'tenant_' . Str::slug($tenantData['slug']),
                'status' => $tenantData['status'],
                'industry' => $tenantData['industry'],
                'country_iso' => $tenantData['country_iso'],
                'timezone' => $tenantData['timezone'],
                'trial_ends_at' => $tenantData['status'] === 'trial' ? now()->addDays(30) : null,
                'activated_at' => $tenantData['status'] === 'active' ? now() : null,
                'data' => $tenantData['data'],
            ]);

            // Créer les configurations centrales
            $this->createTenantConfigurations($tenant, $tenantData);

            // Basculer sur le tenant et créer les données
            $this->command->info("Seeding data for tenant: {$tenant->name}");
            tenancy()->initialize($tenant);
            
            $this->call(TenantDataSeeder::class);
            
            tenancy()->end();
        }

        $this->command->info('✅ All tenants seeded successfully!');
    }

    /**
     * Create tenant configurations in central database
     */
    private function createTenantConfigurations(Tenant $tenant, array $data): void
    {
        // Configuration CommCare
        DB::table('tenant_commcare_configs')->insert([
            'tenant_id' => $tenant->id,
            'commcare_domain' => $data['slug'] . '.commcarehq.org',
            'commcare_api_key' => 'demo_api_key_' . Str::random(32),
            'commcare_username' => 'admin_' . $data['slug'],
            'commcare_project_name' => $data['name'],
            'available_case_types' => json_encode($this->getCaseTypesForIndustry($data['industry'])),
            'default_case_type' => $this->getDefaultCaseType($data['industry']),
            'include_closed_cases' => false,
            'sync_batch_size' => 1000,
            'sync_schedule_time' => '02:30:00',
            'auto_sync_enabled' => true,
            'connection_valid' => true,
            'last_connection_test' => now(),
            'last_successful_sync' => now()->subHours(2),
            'total_cases_synced' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Configuration téléphonie
        DB::table('tenant_phone_configs')->insert([
            'tenant_id' => $tenant->id,
            'country_iso' => $data['country_iso'],
            'country_code' => $this->getCountryCode($data['country_iso']),
            'national_length' => $this->getNationalLength($data['country_iso']),
            'validation_mode' => 'strict',
            'allowed_prefixes' => json_encode($this->getAllowedPrefixes($data['country_iso'])),
            'blocked_prefixes' => json_encode([]),
            'mobile_only' => true,
            'auto_format_e164' => true,
            'fallback_to_lenient' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Mapping des champs
        $this->createFieldMappings($tenant, $data);

        // Subscription
        DB::table('subscriptions')->insert([
            'tenant_id' => $tenant->id,
            'plan' => $data['status'] === 'trial' ? 'trial' : 'basic',
            'status' => $data['status'] === 'trial' ? 'trialing' : 'active',
            'sms_quota_monthly' => $data['status'] === 'trial' ? 1000 : 5000,
            'cases_quota' => $data['status'] === 'trial' ? 500 : 2000,
            'users_quota' => $data['status'] === 'trial' ? 3 : 10,
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
            'trial_ends_at' => $data['status'] === 'trial' ? now()->addDays(30) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Onboarding
        DB::table('tenant_onboarding')->insert([
            'tenant_id' => $tenant->id,
            'current_step' => $data['status'] === 'active' ? 'completed' : 'sms_templates',
            'step_welcome_completed' => true,
            'step_commcare_api_completed' => true,
            'step_field_mapping_completed' => true,
            'step_phone_config_completed' => true,
            'step_sync_setup_completed' => $data['status'] === 'active',
            'step_sms_templates_completed' => $data['status'] === 'active',
            'started_at' => now()->subDays(7),
            'completed_at' => $data['status'] === 'active' ? now()->subDays(1) : null,
            'completion_percentage' => $data['status'] === 'active' ? 100 : 80,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Create field mappings for tenant
     */
    private function createFieldMappings(Tenant $tenant, array $data): void
    {
        $caseType = $this->getDefaultCaseType($data['industry']);
        
        $mappings = [
            ['commcare_property' => 'contact/phone_number', 'system_field' => 'phone', 'is_required' => true, 'display_order' => 1],
            ['commcare_property' => 'name', 'system_field' => 'name', 'is_required' => true, 'display_order' => 2],
            ['commcare_property' => 'edd', 'system_field' => 'primary_date', 'is_required' => true, 'display_order' => 3],
            ['commcare_property' => 'visit_number', 'system_field' => 'sequence_number', 'is_required' => false, 'display_order' => 4],
            ['commcare_property' => 'owner_id', 'system_field' => 'owner_id', 'is_required' => false, 'display_order' => 5],
            ['commcare_property' => 'location', 'system_field' => 'location', 'is_required' => false, 'display_order' => 6],
        ];

        foreach ($mappings as $mapping) {
            DB::table('tenant_field_mappings')->insert([
                'tenant_id' => $tenant->id,
                'case_type' => $caseType,
                'commcare_property' => $mapping['commcare_property'],
                'system_field' => $mapping['system_field'],
                'display_label' => $this->getDisplayLabel($mapping['system_field']),
                'is_required' => $mapping['is_required'],
                'display_order' => $mapping['display_order'],
                'transformation_rules' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Get case types for industry
     */
    private function getCaseTypesForIndustry(string $industry): array
    {
        return match ($industry) {
            'health' => ['woman', 'child', 'patient'],
            'agriculture' => ['farmer', 'cooperative_member', 'extension_worker'],
            'education' => ['student', 'teacher', 'parent'],
            default => ['case'],
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

    /**
     * Get country code
     */
    private function getCountryCode(string $countryIso): string
    {
        return match ($countryIso) {
            'CI' => '+225',
            'ML' => '+223',
            'GH' => '+233',
            default => '+1',
        };
    }

    /**
     * Get national length
     */
    private function getNationalLength(string $countryIso): int
    {
        return match ($countryIso) {
            'CI' => 10,
            'ML' => 8,
            'GH' => 9,
            default => 10,
        };
    }

    /**
     * Get allowed prefixes
     */
    private function getAllowedPrefixes(string $countryIso): array
    {
        return match ($countryIso) {
            'CI' => ['01', '02', '03', '05', '06', '07', '08', '09'],
            'ML' => ['60', '61', '62', '63', '64', '65', '66', '67', '68', '69', '70', '71', '72', '73', '74', '75', '76', '77', '78', '79'],
            'GH' => ['20', '23', '24', '25', '26', '27', '28', '50', '54', '55', '56', '57', '59'],
            default => ['0'],
        };
    }

    /**
     * Get display label
     */
    private function getDisplayLabel(string $systemField): string
    {
        return match ($systemField) {
            'phone' => 'Numéro de téléphone',
            'name' => 'Nom complet',
            'primary_date' => 'Date principale',
            'sequence_number' => 'Numéro de séquence',
            'owner_id' => 'ID propriétaire',
            'location' => 'Localisation',
            default => ucfirst(str_replace('_', ' ', $systemField)),
        };
    }
}
