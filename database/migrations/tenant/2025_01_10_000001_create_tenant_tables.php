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
        // 1. cases - Table principale des cas (générique, pas "women")
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_id')->unique()->index();
            $table->string('case_type')->index();
            $table->string('case_name');
            $table->string('external_id')->nullable();
            $table->string('contact_phone_number')->nullable();
            $table->string('formatted_phone_number')->nullable()->index();
            $table->boolean('phone_valid')->default(false)->index();
            $table->text('phone_validation_error')->nullable();
            $table->date('primary_date')->nullable()->index();
            $table->date('secondary_date')->nullable();
            $table->integer('sequence_number')->nullable();
            $table->string('owner_id')->nullable()->index();
            $table->string('owner_name')->nullable();
            $table->string('location_id')->nullable();
            $table->string('location_name')->nullable();
            $table->timestamp('case_opened_at')->nullable();
            $table->timestamp('case_closed_at')->nullable();
            $table->boolean('is_closed')->default(false)->index();
            $table->boolean('is_eligible_for_sms')->default(false)->index();
            $table->json('properties')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->string('sync_batch_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['case_type', 'is_closed']);
            $table->index(['case_type', 'primary_date']);
            $table->index(['is_eligible_for_sms', 'primary_date']);
        });

        // 2. sms_templates - Modèles de SMS configurables
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('case_type')->nullable()->index();
            $table->enum('trigger_type', ['date_based', 'sequence_based', 'status_change', 'custom_condition'])->default('date_based');
            $table->integer('days_before')->nullable();
            $table->time('sending_time')->default('09:00:00');
            $table->time('window_start')->nullable();
            $table->time('window_end')->nullable();
            $table->integer('target_sequence_number')->nullable();
            $table->text('message_template');
            $table->json('available_variables')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('priority')->default(0);
            $table->json('conditions')->nullable();
            $table->integer('total_sent')->default(0);
            $table->integer('total_delivered')->default(0);
            $table->integer('total_failed')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['trigger_type', 'is_active']);
        });

        // 3. sms_queue - Queue des SMS à envoyer
        Schema::create('sms_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->foreignId('sms_template_id')->constrained('sms_templates')->cascadeOnDelete();
            $table->string('phone_number');
            $table->text('message');
            $table->integer('message_length')->nullable();
            $table->integer('sms_parts')->default(1);
            $table->timestamp('scheduled_at')->index();
            $table->timestamp('send_after')->nullable();
            $table->timestamp('send_before')->nullable();
            $table->enum('status', ['pending', 'queued', 'sent', 'delivered', 'failed', 'cancelled'])->default('pending')->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('gateway_provider')->nullable();
            $table->string('gateway_message_id')->nullable()->index();
            $table->text('gateway_response')->nullable();
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            $table->integer('cost_cents')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['status', 'scheduled_at']);
            $table->index(['case_id', 'status']);
        });

        // 4. sms_delivery_reports - Rapports de livraison SMS
        Schema::create('sms_delivery_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sms_queue_id')->constrained('sms_queue')->cascadeOnDelete();
            $table->string('gateway_message_id')->index();
            $table->string('gateway_status');
            $table->text('gateway_raw_payload');
            $table->timestamp('reported_at');
            $table->timestamps();
        });

        // 5. sync_logs - Logs de synchronisation
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['commcare_full', 'commcare_delta', 'sms_generation', 'phone_validation'])->index();
            $table->enum('status', ['running', 'completed', 'partial', 'failed'])->index();
            $table->integer('total_records')->default(0);
            $table->integer('processed_records')->default(0);
            $table->integer('successful_records')->default(0);
            $table->integer('failed_records')->default(0);
            $table->integer('skipped_records')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->text('error_message')->nullable();
            $table->json('error_details')->nullable();
            $table->string('batch_id')->nullable()->index();
            $table->string('triggered_by')->nullable();
            $table->json('parameters')->nullable();
            $table->timestamps();
            $table->index(['type', 'status', 'started_at']);
        });

        // 6. custom_fields - Champs personnalisés par type de cas
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('case_type');
            $table->string('field_name');
            $table->string('field_label');
            $table->enum('field_type', ['text', 'number', 'date', 'boolean', 'select', 'json'])->default('text');
            $table->json('field_options')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_searchable')->default(false);
            $table->timestamps();
            $table->unique(['case_type', 'field_name']);
        });

        // 7. webhooks - Configuration des webhooks
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->enum('method', ['POST', 'GET', 'PUT'])->default('POST');
            $table->json('events');
            $table->string('secret')->nullable();
            $table->json('headers')->nullable();
            $table->integer('timeout_seconds')->default(30);
            $table->integer('max_retries')->default(3);
            $table->boolean('is_active')->default(true);
            $table->integer('total_calls')->default(0);
            $table->integer('successful_calls')->default(0);
            $table->integer('failed_calls')->default(0);
            $table->timestamp('last_called_at')->nullable();
            $table->timestamps();
        });

        // 8. webhook_logs - Logs des appels webhook
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->constrained('webhooks')->cascadeOnDelete();
            $table->string('event');
            $table->text('payload');
            $table->integer('http_status')->nullable();
            $table->text('response')->nullable();
            $table->enum('status', ['success', 'failed', 'timeout'])->index();
            $table->integer('retry_count')->default(0);
            $table->timestamp('called_at');
            $table->timestamps();
            $table->index(['webhook_id', 'created_at']);
        });

        // 9. reports_cache - Cache des rapports générés
        Schema::create('reports_cache', function (Blueprint $table) {
            $table->id();
            $table->string('report_type');
            $table->date('report_date')->index();
            $table->json('data');
            $table->timestamp('generated_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['report_type', 'report_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Suppression dans l'ordre inverse des dépendances
        Schema::dropIfExists('reports_cache');
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('custom_fields');
        Schema::dropIfExists('sync_logs');
        Schema::dropIfExists('sms_delivery_reports');
        Schema::dropIfExists('sms_queue');
        Schema::dropIfExists('sms_templates');
        Schema::dropIfExists('cases');
    }
};
