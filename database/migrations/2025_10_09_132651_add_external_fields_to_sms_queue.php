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
        Schema::table('sms_queue', function (Blueprint $table) {
            if (!Schema::hasColumn('sms_queue', 'external_id')) {
                $table->string('external_id')->nullable()->after('status')->comment('Africa\'s Talking Message ID');
            }
            if (!Schema::hasColumn('sms_queue', 'cost')) {
                $table->decimal('cost', 10, 2)->nullable()->after('external_id')->comment('SMS cost in FCFA');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_queue', function (Blueprint $table) {
            $table->dropColumn(['external_id', 'cost']);
        });
    }
};
