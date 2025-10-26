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
        // Rendre organization_id NOT NULL dans la table women
        Schema::table('women', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
        });

        // Rendre organization_id NOT NULL dans la table sms_queue
        Schema::table('sms_queue', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurer organization_id nullable dans la table women
        Schema::table('women', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->change();
        });

        // Restaurer organization_id nullable dans la table sms_queue
        Schema::table('sms_queue', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->change();
        });
    }
};
