<?php

declare(strict_types=1);

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
            $table->string('default_send_time')->default('08:00')->after('timezone');
            $table->string('send_window_start')->default('06:00')->after('default_send_time');
            $table->string('send_window_end')->default('21:00')->after('send_window_start');
            $table->string('default_language')->default('fr')->after('send_window_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'default_send_time',
                'send_window_start',
                'send_window_end',
                'default_language',
            ]);
        });
    }
};
