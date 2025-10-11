<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('women', function (Blueprint $table) {
            $table->id();
            
            // Données CommCare
            $table->string('case_id')->unique()->comment('ID unique CommCare');
            $table->string('case_name')->comment('Nom complet de la FE');
            $table->integer('client_age')->nullable();
            
            // Contacts
            $table->string('contact_phone_number')->nullable();
            $table->string('husband_phone_number')->nullable();
            $table->boolean('contact_phone_number_is_verified')->default(false);
            $table->boolean('consent_sms_yes')->default(false);
            
            // Suivi CPN
            $table->integer('anc_counter')->default(0)->comment('Nombre de CPN déjà réalisées');
            $table->date('next_visit_date')->nullable()->comment('Date du prochain RDV');
            $table->date('two_days_before_next_visit_date')->nullable()->comment('J-2 avant RDV');
            
            // Localisation
            $table->string('structure_sanitaire')->nullable();
            $table->string('district_sanitaire')->nullable();
            $table->string('region_sanitaire')->nullable();
            
            // Stats & Meta
            $table->integer('sms_reminder_counter')->default(0)->comment('Total SMS envoyés');
            $table->json('raw_properties')->nullable()->comment('Données brutes CommCare');
            $table->boolean('closed')->default(false)->comment('Dossier fermé/actif');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour performance
            $table->index('next_visit_date');
            $table->index('two_days_before_next_visit_date');
            $table->index(['consent_sms_yes', 'contact_phone_number_is_verified']);
            $table->index('closed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('women');
    }
};