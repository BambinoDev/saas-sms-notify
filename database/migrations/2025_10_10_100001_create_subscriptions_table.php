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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->enum('plan', ['starter', 'pro', 'enterprise'])->default('starter');
            $table->enum('status', ['active', 'trial', 'past_due', 'cancelled', 'expired'])->default('trial');
            $table->integer('sms_limit')->default(1000); // Limite SMS/mois
            $table->integer('sms_used')->default(0); // SMS utilisés ce mois
            $table->integer('users_limit')->default(1);
            $table->integer('structures_limit')->default(1);
            $table->decimal('price', 10, 2)->default(0);
            $table->string('stripe_subscription_id')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('organization_id');
            $table->index('status');
            $table->index('current_period_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

