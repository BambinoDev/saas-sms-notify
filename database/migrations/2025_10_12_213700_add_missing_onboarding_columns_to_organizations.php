<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Cette migration COMPLÈTE la migration 2025_10_11_120000_add_onboarding_columns_to_organizations_table
     * en ajoutant les colonnes manquantes pour le nouveau workflow d'onboarding.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // ============================================
            // ÉTAPE 2 : COMPANY (Nouvelles colonnes)
            // ============================================
            $table->text('project_description')->nullable()->after('organization_type')
                ->comment('Description du projet CommCare');

            // ============================================
            // ÉTAPE 3 : COMMCARE (Colonnes manquantes)
            // ============================================
            $table->string('commcare_email')->nullable()->after('commcare_domain')
                ->comment('Email du compte CommCare');
            
            $table->string('commcare_project_space')->nullable()->after('commcare_email')
                ->comment('Domain du projet (alias de commcare_domain pour compatibilité)');
            
            $table->string('commcare_case_type')->nullable()->after('commcare_app_id')
                ->comment('Type de case traité (ex: woman, child, patient)');

            // ============================================
            // ÉTAPE 4 : MAPPING (Nouvelles colonnes détaillées)
            // ============================================
            $table->json('case_properties_mapping')->nullable()->after('field_mappings')
                ->comment('Liste des propriétés CommCare sélectionnées (ex: ["case_id", "case_name", "phone_number"])');
            
            $table->string('phone_number_field')->nullable()->after('case_properties_mapping')
                ->comment('Nom du champ contenant le numéro de téléphone (OBLIGATOIRE)');
            
            // Conditions d'éligibilité (optionnel)
            $table->string('eligibility_field')->nullable()->after('phone_number_field')
                ->comment('Champ à vérifier pour éligibilité (ex: pregnant_status)');
            
            $table->enum('eligibility_condition', ['equals', 'not_equals', 'contains', 'not_contains'])->nullable()
                ->after('eligibility_field')
                ->comment('Type de condition (égal à, différent de, contient, ne contient pas)');
            
            $table->string('eligibility_value')->nullable()->after('eligibility_condition')
                ->comment('Valeur attendue pour la condition (ex: yes, true, enrolled)');

            // ============================================
            // INDEX POUR PERFORMANCE (nouveaux)
            // ============================================
            $table->index('commcare_project_space');
            $table->index('commcare_case_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Supprimer les index d'abord
            $table->dropIndex(['commcare_project_space']);
            $table->dropIndex(['commcare_case_type']);

            // Supprimer toutes les nouvelles colonnes
            $table->dropColumn([
                'project_description',
                'commcare_email',
                'commcare_project_space',
                'commcare_case_type',
                'case_properties_mapping',
                'phone_number_field',
                'eligibility_field',
                'eligibility_condition',
                'eligibility_value',
            ]);
        });
    }
};
