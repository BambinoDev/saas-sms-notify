<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_generation_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('rule_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()
                ->comment('User qui a déclenché (si manuel)');
            
            // EXÉCUTION
            $table->string('trigger_type', 50)->default('automatic'); // automatic/manual
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            
            // RÉSULTATS
            $table->string('status', 50); // success/partial/failed
            $table->integer('total_cases_evaluated')->default(0);
            $table->integer('total_sms_generated')->default(0);
            $table->integer('total_duplicates_skipped')->default(0);
            $table->integer('total_errors')->default(0);
            
            // ERREURS
            $table->text('error_message')->nullable();
            $table->text('error_trace')->nullable();
            
            // METADATA
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->onDelete('cascade');
            
            $table->foreign('rule_id')
                ->references('id')
                ->on('sms_rules')
                ->onDelete('set null');
            
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            
            // Index
            $table->index(['organization_id', 'trigger_type']);
            $table->index('rule_id');
            $table->index('status');
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_generation_logs');
    }
};