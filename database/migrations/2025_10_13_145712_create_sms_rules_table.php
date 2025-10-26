<?php

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
        Schema::create('sms_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('sms_template_id')->nullable()->constrained('sms_templates')->onDelete('set null');
            
            // Informations de base
            $table->string('name'); // Ex: "Rappel RDV J-1"
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Conditions d'envoi
            $table->string('trigger_field'); // Ex: "next_visit_date"
            $table->enum('trigger_condition', ['equals', 'before', 'after', 'between']); // Ex: "before"
            $table->string('trigger_value'); // Ex: "1" (pour "1 jour avant")
            $table->enum('trigger_unit', ['minutes', 'hours', 'days', 'weeks'])->default('days'); // Ex: "days"
            
            // Heure d'envoi (optionnel)
            $table->time('send_time')->nullable(); // Ex: "09:00:00" (9h du matin)
            
            // Statistiques
            $table->integer('total_sent')->default(0);
            $table->integer('total_failed')->default(0);
            $table->timestamp('last_executed_at')->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index(['organization_id', 'is_active']);
            $table->index('trigger_field');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_rules');
    }
};
