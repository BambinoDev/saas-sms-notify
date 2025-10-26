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
        Schema::table('women', function (Blueprint $table) {
            // Supprimer l'ancienne contrainte unique sur case_id seul
            $table->dropUnique('women_case_id_unique');
            
            // Ajouter une nouvelle contrainte unique sur (organization_id, case_id)
            $table->unique(['organization_id', 'case_id'], 'women_org_case_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('women', function (Blueprint $table) {
            // Supprimer la contrainte unique composite
            $table->dropUnique('women_org_case_unique');
            
            // Restaurer l'ancienne contrainte unique sur case_id seul
            $table->unique('case_id', 'women_case_id_unique');
        });
    }
};
