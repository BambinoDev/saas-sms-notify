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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->nullable()->unique(); // custom domain
            $table->text('logo_url')->nullable();
            $table->string('primary_color')->default('#3B82F6');
            $table->string('secondary_color')->default('#10B981');
            $table->enum('status', ['active', 'suspended', 'trial', 'cancelled'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->json('settings')->nullable(); // Settings custom
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('trial_ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};

