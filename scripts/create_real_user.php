<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Organization;
use App\Models\User;

echo "=== CRÉATION UTILISATEUR RÉEL ===\n\n";

// Récupérer l'organisation CH
$org = Organization::find(5);
if (!$org) {
    echo "❌ Organisation CH (ID: 5) non trouvée\n";
    exit(1);
}

echo "Organisation trouvée : {$org->name}\n";

// Créer l'utilisateur
$user = User::create([
    'name' => 'Admin CH',
    'email' => 'admin.ch@savethechildren.org',
    'password' => bcrypt('SecurePassword123!'),
    'email_verified_at' => now(),
]);

echo "Utilisateur créé : {$user->name} ({$user->email})\n";

// Associer l'utilisateur à l'organisation
$org->users()->attach($user->id);

echo "Utilisateur associé à l'organisation {$org->name}\n\n";

echo "=== CRÉDENTIALS DE CONNEXION ===\n";
echo "Email: admin.ch@savethechildren.org\n";
echo "Password: SecurePassword123!\n";
echo "Organisation: {$org->name}\n\n";

echo "✅ Utilisateur créé avec succès !\n";
