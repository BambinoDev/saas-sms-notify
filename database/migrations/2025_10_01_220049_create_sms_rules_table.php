<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sms_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nom de la règle ex: Rappel J-2');
            $table->string('type')->unique()->comment('Identifiant technique ex: j-2, jour-j');
            $table->integer('days_before')->comment('Jours avant RDV (0 = jour même, 2 = J-2)');
            $table->time('sending_time')->default('08:00')->comment('Heure d\'envoi');
            $table->text('template')->comment('Template du message');
            $table->boolean('active')->default(true)->comment('Règle active ou non');
            $table->integer('priority')->default(0)->comment('Ordre d\'exécution');
            $table->timestamps();
            
            $table->index(['active', 'days_before']);
        });

        // Insérer règles par défaut
        DB::table('sms_rules')->insert([
            [
                'name' => 'Rappel J-2',
                'type' => 'j-2',
                'days_before' => 2,
                'sending_time' => '08:00',
                'template' => "Bonjour Mme {case_name}, venez après-demain au Centre de Santé pour votre {anc_number}ème Consultation Prénatale. Ne manquez pas ce Rendez-vous !",
                'active' => true,
                'priority' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rappel Jour-J',
                'type' => 'jour-j',
                'days_before' => 0,
                'sending_time' => '06:00',
                'template' => "Bonjour Mme {case_name}, n'oubliez pas votre RDV aujourd'hui au centre santé pour votre {anc_number}ème Consultation Prénatale. Nous vous attendons !",
                'active' => true,
                'priority' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_rules');
    }
};