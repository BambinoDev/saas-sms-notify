<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // ÉTAPE 2 : Company Info
            $table->string('organization_type')->nullable()->after('settings');
            $table->string('sector')->nullable()->after('organization_type');
            $table->string('timezone')->default('Africa/Abidjan')->after('sector');
            $table->string('team_size')->nullable()->after('timezone');
            
            // ÉTAPE 3 : CommCare Config
            $table->string('commcare_domain')->nullable()->after('team_size');
            $table->string('commcare_project_name')->nullable()->after('commcare_domain');
            $table->text('commcare_api_key')->nullable()->after('commcare_project_name');
            $table->string('commcare_app_id')->nullable()->after('commcare_api_key');
            
            // ÉTAPE 4 : Phone Validation Config
            $table->string('primary_country', 2)->default('CI')->after('commcare_app_id');
            $table->json('allowed_prefixes')->nullable()->after('primary_country');
            $table->enum('phone_validation_mode', ['strict', 'flexible'])->default('strict')->after('allowed_prefixes');
            $table->boolean('mobile_only')->default(true)->after('phone_validation_mode');
            $table->boolean('auto_format_e164')->default(true)->after('mobile_only');
            
            // ÉTAPE 5 : Field Mappings
            $table->json('field_mappings')->nullable()->after('auto_format_e164');
            
            // Tracking Onboarding
            $table->boolean('onboarding_completed')->default(false)->after('field_mappings');
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_completed');
            $table->unsignedTinyInteger('onboarding_step')->default(1)->after('onboarding_completed_at');
            
            // Indexes pour performance
            $table->index('onboarding_completed');
            $table->index('commcare_domain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Supprimer les indexes d'abord
            $table->dropIndex(['commcare_domain']);
            $table->dropIndex(['onboarding_completed']);
            
            // Supprimer les colonnes dans l'ordre inverse
            $table->dropColumn([
                'onboarding_step',
                'onboarding_completed_at',
                'onboarding_completed',
                'field_mappings',
                'auto_format_e164',
                'mobile_only',
                'phone_validation_mode',
                'allowed_prefixes',
                'primary_country',
                'commcare_app_id',
                'commcare_api_key',
                'commcare_project_name',
                'commcare_domain',
                'team_size',
                'timezone',
                'sector',
                'organization_type',
            ]);
        });
    }
};

