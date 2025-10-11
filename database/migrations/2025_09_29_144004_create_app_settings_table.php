<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('type')->default('string')->comment('string, json, boolean');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        
        // Insérer templates par défaut
        DB::table('app_settings')->insert([
            [
                'key' => 'sms_template_j_minus_2',
                'value' => 'Bonjour Mme {case_name}, venez après-demain au Centre de Santé pour votre {anc_number}ème Consultation Prénatale. Ne manquez pas ce Rendez-vous !',
                'type' => 'string',
                'description' => 'Template SMS J-2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'sms_template_jour_j',
                'value' => 'Bonjour Mme {case_name}, venez aujourd\'hui au centre santé pour votre {anc_number}ème Consultation Prénatale. Nous vous attendons !',
                'type' => 'string',
                'description' => 'Template SMS Jour-J',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'sms_sending_time',
                'value' => '08:00',
                'type' => 'string',
                'description' => 'Heure d\'envoi des SMS',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'commcare_last_sync',
                'value' => '',
                'type' => 'string',
                'description' => 'Dernière synchronisation CommCare',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};