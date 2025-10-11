<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Renommer l'ancienne table subscriptions (liée aux tenants)
        // pour ne pas conflit avec la nouvelle table (liée aux organizations)
        Schema::rename('subscriptions', 'tenant_subscriptions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('tenant_subscriptions', 'subscriptions');
    }
};


