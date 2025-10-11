<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting database seeding...');

        // Créer un utilisateur admin central (s'il n'existe pas)
        if (!User::where('email', 'admin@notify-sms.local')->exists()) {
            User::factory()->create([
                'name' => 'Admin Central',
                'email' => 'admin@notify-sms.local',
            ]);
        }

        // Seeder les tenants avec leurs données
        $this->call([
            TenantSeeder::class,
            // TenantDataSeeder sera appelé automatiquement par TenantSeeder pour chaque tenant
        ]);

        $this->command->info('✅ Database seeding completed successfully!');
    }
}
