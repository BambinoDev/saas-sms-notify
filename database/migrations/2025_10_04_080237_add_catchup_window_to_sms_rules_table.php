<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_rules', function (Blueprint $table) {
            $table->time('window_start')->default('06:00')->comment('Heure début fenêtre de rattrapage');
            $table->time('window_end')->default('12:00')->comment('Heure fin fenêtre de rattrapage');
            $table->boolean('window_enabled')->default(true)->comment('Activer/désactiver la fenêtre');
        });
    }

    public function down(): void
    {
        Schema::table('sms_rules', function (Blueprint $table) {
            $table->dropColumn(['window_start', 'window_end', 'window_enabled']);
        });
    }
};