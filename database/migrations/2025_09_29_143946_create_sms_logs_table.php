<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('sms_queue_id')->constrained('sms_queue')->onDelete('cascade');
            $table->foreignId('woman_id')->constrained()->onDelete('cascade');
            
            // Détails SMS
            $table->string('phone_number', 20);
            $table->text('message_content');
            $table->enum('sms_type', ['j-2', 'jour-j']);
            $table->date('appointment_date')->comment('Date du RDV concerné');
            
            // Résultat envoi
            $table->enum('status', ['sent', 'delivered', 'failed']);
            $table->text('gateway_response')->nullable()->comment('Réponse de l\'app Flutter');
            $table->text('error_details')->nullable();
            
            // Timestamps
            $table->timestamp('sent_at');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            
            // Index pour analytics
            $table->index('status');
            $table->index('appointment_date');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};