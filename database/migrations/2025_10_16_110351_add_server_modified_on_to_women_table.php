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
            // Ajouter la colonne server_modified_on après case_id
            $table->timestamp('server_modified_on')->nullable()->after('case_id');
            
            // Ajouter un index pour les requêtes de synchronisation
            $table->index('server_modified_on');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('women', function (Blueprint $table) {
            $table->dropIndex(['server_modified_on']);
            $table->dropColumn('server_modified_on');
        });
    }
};
