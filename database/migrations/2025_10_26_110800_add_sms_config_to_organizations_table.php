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
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('sms_environment')->default('sandbox')->after('name');
            $table->string('sms_username')->nullable()->after('sms_environment');
            $table->text('sms_api_key')->nullable()->after('sms_username');
            $table->string('sms_sender_id')->default('S-REMIND')->after('sms_api_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['sms_environment', 'sms_username', 'sms_api_key', 'sms_sender_id']);
        });
    }
};
