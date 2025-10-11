<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_queue', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('woman_id')->constrained()->onDelete('cascade');
            
            // Contenu SMS
            $table->string('recipient_phone', 20);
            $table->text('message_content');
            $table->enum('sms_type', ['j-2', 'jour-j'])->comment('Type de rappel');
            
            // Planification
            $table->date('scheduled_date')->comment('Date du RDV concerné');
            $table->timestamp('scheduled_at')->comment('Quand envoyer le SMS');
            
            // Statut & Suivi
            $table->enum('status', ['pending', 'sent', 'failed', 'delivered'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->integer('retry_count')->default(0);
            $table->text('error_message')->nullable();
            
            $table->timestamps();
            
            // Index pour fetch API Flutter
            $table->index(['status', 'scheduled_at']);
            $table->index('scheduled_date');
            
            // Contrainte unicité (éviter doublons pour un même RDV)
            $table->unique(['woman_id', 'scheduled_date', 'sms_type'], 'unique_sms_per_appointment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_queue');
    }
};