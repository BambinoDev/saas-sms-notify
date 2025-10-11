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
        Schema::table('sms_rules', function (Blueprint $table) {
            // Drop the unique constraint on 'type'
            $table->dropUnique('sms_rules_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_rules', function (Blueprint $table) {
            // Restore the unique constraint if needed
            $table->unique('type');
        });
    }
};
