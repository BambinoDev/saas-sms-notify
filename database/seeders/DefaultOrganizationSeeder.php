<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Subscription;
use App\Models\User;
use App\Models\CaseModel;
use App\Models\SmsQueue;
use App\Models\SmsRule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DefaultOrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $this->command->info('🚀 Création de l\'organisation par défaut...');

            // Vérifier si l'organisation existe déjà
            $organization = Organization::where('slug', 'ministere-sante-ci')->first();

            if ($organization) {
                $this->command->warn('⚠️  L\'organisation par défaut existe déjà.');
                DB::rollBack();
                return;
            }

            // Créer organisation par défaut
            $organization = Organization::create([
                'name' => 'Ministère de la Santé - Côte d\'Ivoire',
                'slug' => 'ministere-sante-ci',
                'status' => 'active',
                'primary_color' => '#FF7900', // Orange CI
                'secondary_color' => '#009E60', // Vert CI
                'settings' => [
                    'country' => 'Côte d\'Ivoire',
                    'timezone' => 'Africa/Abidjan',
                    'language' => 'fr',
                ],
            ]);

            $this->command->info("✅ Organisation créée : {$organization->name}");

            // Créer subscription Enterprise (illimitée)
            $subscription = Subscription::create([
                'organization_id' => $organization->id,
                'plan' => 'enterprise',
                'status' => 'active',
                'sms_limit' => 999999,
                'sms_used' => 0,
                'users_limit' => 999,
                'structures_limit' => 999,
                'price' => 0.00, // Gratuit pour gouvernement
                'current_period_start' => Carbon::now(),
                'current_period_end' => Carbon::now()->addYear(),
            ]);

            $this->command->info("✅ Subscription créée : Plan {$subscription->plan}");

            // Attacher tous les users existants
            $users = User::all();
            $usersCount = 0;

            foreach ($users as $user) {
                $organization->users()->attach($user->id, [
                    'role' => $user->id === 1 ? 'owner' : 'admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $usersCount++;
            }

            $this->command->info("✅ {$usersCount} utilisateurs attachés à l'organisation");

            // Migrer toutes les données existantes vers l'organisation
            
            // 1. Migrer Cases (Women)
            $casesUpdated = CaseModel::whereNull('organization_id')
                ->update(['organization_id' => $organization->id]);
            $this->command->info("✅ {$casesUpdated} cases migrés");

            // 2. Migrer SMS Queue
            $smsQueueUpdated = SmsQueue::whereNull('organization_id')
                ->update(['organization_id' => $organization->id]);
            $this->command->info("✅ {$smsQueueUpdated} SMS en queue migrés");

            // 3. Migrer SMS Rules
            $rulesUpdated = SmsRule::whereNull('organization_id')
                ->update(['organization_id' => $organization->id]);
            $this->command->info("✅ {$rulesUpdated} règles SMS migrées");

            DB::commit();

            // Afficher le résumé
            $this->command->newLine();
            $this->command->info('════════════════════════════════════════════════════');
            $this->command->info('✨ MIGRATION MULTI-TENANT RÉUSSIE !');
            $this->command->info('════════════════════════════════════════════════════');
            $this->command->info('📊 Résumé :');
            $this->command->info("   • Organisation : {$organization->name}");
            $this->command->info("   • Slug : {$organization->slug}");
            $this->command->info("   • Status : {$organization->status}");
            $this->command->info("   • Plan : {$subscription->plan}");
            $this->command->info("   • Limite SMS : " . number_format($subscription->sms_limit));
            $this->command->info("   • Utilisateurs : {$usersCount}");
            $this->command->info("   • Cases migrés : {$casesUpdated}");
            $this->command->info("   • SMS queue migrés : {$smsQueueUpdated}");
            $this->command->info("   • Règles migrées : {$rulesUpdated}");
            $this->command->info('════════════════════════════════════════════════════');
            $this->command->newLine();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Erreur lors de la migration : ' . $e->getMessage());
            $this->command->error($e->getTraceAsString());
            throw $e;
        }
    }
}

