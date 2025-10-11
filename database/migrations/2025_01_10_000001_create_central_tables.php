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
        // 1. tenants - Table principale des organisations
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('database_schema')->unique();
            $table->enum('status', ['active', 'suspended', 'trial', 'cancelled'])->default('trial')->index();
            $table->string('industry')->nullable();
            $table->string('country_iso', 2)->nullable();
            $table->string('timezone')->default('UTC');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('industry');
        });

        // 2. domains - Domaines personnalisés des tenants
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('domain')->unique();
            $table->boolean('is_primary')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'is_primary']);
        });

        // 3. users - Utilisateurs centraux (cross-tenant) - Skip if exists
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('locale')->default('fr');
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 4. tenant_users - Relation many-to-many entre tenants et utilisateurs
        Schema::create('tenant_users', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['owner', 'admin', 'manager', 'user'])->default('user');
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'user_id']);
            $table->index(['user_id', 'role']);
        });

        // 5. tenant_commcare_configs - Configuration CommCare par tenant
        Schema::create('tenant_commcare_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('commcare_domain')->index();
            $table->text('commcare_api_key');
            $table->string('commcare_username')->nullable();
            $table->string('commcare_project_name')->nullable();
            $table->json('available_case_types')->nullable();
            $table->string('default_case_type')->nullable();
            $table->boolean('include_closed_cases')->default(false);
            $table->integer('sync_batch_size')->default(5000);
            $table->time('sync_schedule_time')->default('02:30:00');
            $table->boolean('auto_sync_enabled')->default(true);
            $table->boolean('connection_valid')->default(false);
            $table->timestamp('last_connection_test')->nullable();
            $table->timestamp('last_successful_sync')->nullable();
            $table->integer('total_cases_synced')->default(0);
            $table->text('last_sync_error')->nullable();
            $table->timestamps();
            $table->unique('tenant_id');
        });

        // 6. tenant_phone_configs - Configuration des numéros de téléphone par tenant
        Schema::create('tenant_phone_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('country_iso', 2)->index();
            $table->string('country_code', 5);
            $table->integer('national_length');
            $table->enum('validation_mode', ['strict', 'lenient'])->default('strict');
            $table->json('allowed_prefixes')->nullable();
            $table->json('blocked_prefixes')->nullable();
            $table->boolean('mobile_only')->default(true);
            $table->boolean('auto_format_e164')->default(true);
            $table->json('legacy_transformations')->nullable();
            $table->text('custom_regex')->nullable();
            $table->boolean('fallback_to_lenient')->default(true);
            $table->timestamps();
            $table->unique('tenant_id');
        });

        // 7. tenant_field_mappings - Mapping des champs CommCare vers les champs système
        Schema::create('tenant_field_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('case_type');
            $table->string('commcare_property');
            $table->enum('system_field', ['phone', 'name', 'primary_date', 'secondary_date', 'sequence_number', 'owner_id', 'location', 'status', 'custom_field']);
            $table->string('display_label')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('display_order')->default(0);
            $table->json('transformation_rules')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'case_type', 'commcare_property'], 'unique_mapping');
            $table->index(['tenant_id', 'case_type', 'system_field']);
        });

        // 8. subscriptions - Abonnements et plans des tenants
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_customer_id')->unique()->nullable();
            $table->string('stripe_subscription_id')->unique()->nullable();
            $table->enum('plan', ['trial', 'basic', 'pro', 'enterprise'])->default('trial');
            $table->enum('status', ['active', 'trialing', 'past_due', 'cancelled', 'unpaid'])->default('trialing')->index();
            $table->integer('sms_quota_monthly')->default(5000);
            $table->integer('cases_quota')->default(1000);
            $table->integer('users_quota')->default(5);
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->unique('tenant_id');
            $table->index(['status', 'current_period_end']);
        });

        // 9. usage_records - Enregistrement de l'utilisation par tenant
        Schema::create('usage_records', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->date('period_date')->index();
            $table->integer('sms_sent')->default(0);
            $table->integer('sms_delivered')->default(0);
            $table->integer('sms_failed')->default(0);
            $table->integer('active_cases')->default(0);
            $table->integer('api_calls')->default(0);
            $table->integer('storage_mb')->default(0);
            $table->integer('sms_cost_cents')->default(0);
            $table->integer('overage_cost_cents')->default(0);
            $table->timestamps();
            $table->unique(['tenant_id', 'period_date']);
            $table->index(['period_date', 'tenant_id']);
        });

        // 10. tenant_onboarding - Processus d'onboarding des nouveaux tenants
        Schema::create('tenant_onboarding', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->enum('current_step', ['welcome', 'commcare_api', 'field_mapping', 'phone_config', 'sync_setup', 'sms_templates', 'completed'])->default('welcome');
            $table->boolean('step_welcome_completed')->default(false);
            $table->boolean('step_commcare_api_completed')->default(false);
            $table->boolean('step_field_mapping_completed')->default(false);
            $table->boolean('step_phone_config_completed')->default(false);
            $table->boolean('step_sync_setup_completed')->default(false);
            $table->boolean('step_sms_templates_completed')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('completion_percentage')->default(0);
            $table->timestamps();
            $table->unique('tenant_id');
        });

        // 11. activity_logs - Journal d'activité pour audit et monitoring
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event');
            $table->text('description')->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('event');
        });

        // 12. failed_jobs - Jobs échoués (Laravel standard) - Skip if exists
        if (!Schema::hasTable('failed_jobs')) {
            Schema::create('failed_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
        }

        // 13. jobs - Queue des jobs (Laravel standard) - Skip if exists
        if (!Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table) {
                $table->id();
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
                $table->index(['queue', 'reserved_at']);
            });
        }

        // 14. password_reset_tokens - Tokens de réinitialisation de mot de passe - Skip if exists
        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        // 15. sessions - Sessions utilisateur (Laravel standard) - Skip if exists
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Suppression dans l'ordre inverse des dépendances
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('tenant_onboarding');
        Schema::dropIfExists('usage_records');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('tenant_field_mappings');
        Schema::dropIfExists('tenant_phone_configs');
        Schema::dropIfExists('tenant_commcare_configs');
        Schema::dropIfExists('tenant_users');
        Schema::dropIfExists('users');
        Schema::dropIfExists('domains');
        Schema::dropIfExists('tenants');
    }
};
