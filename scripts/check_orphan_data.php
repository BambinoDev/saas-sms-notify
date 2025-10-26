<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CaseModel;
use App\Models\SmsQueue;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== AUDIT DONNÉES ORPHELINES ===\n\n";

// Cases sans organization_id
$orphanCases = CaseModel::whereNull('organization_id')->count();
$totalCases = CaseModel::count();
echo "Cases orphelins : {$orphanCases} / {$totalCases}\n";

// SMS sans organization_id
$orphanSms = SmsQueue::whereNull('organization_id')->count();
$totalSms = SmsQueue::count();
echo "SMS orphelins : {$orphanSms} / {$totalSms}\n";

// Users de test
$testUsers = User::where('email', 'LIKE', '%@example.com')->count();
echo "Utilisateurs de test : {$testUsers}\n\n";

// Cases par organisation
echo "=== RÉPARTITION PAR ORGANISATION ===\n";
$casesByOrg = CaseModel::selectRaw('organization_id, COUNT(*) as count')
    ->groupBy('organization_id')
    ->get();

foreach ($casesByOrg as $row) {
    $orgId = $row->organization_id ?? 'NULL';
    echo "Organisation {$orgId} : {$row->count} cases\n";
}

// SMS par organisation
echo "\n=== RÉPARTITION SMS PAR ORGANISATION ===\n";
$smsByOrg = SmsQueue::selectRaw('organization_id, COUNT(*) as count')
    ->groupBy('organization_id')
    ->get();

foreach ($smsByOrg as $row) {
    $orgId = $row->organization_id ?? 'NULL';
    echo "Organisation {$orgId} : {$row->count} SMS\n";
}

// Utilisateurs par organisation (via table pivot)
echo "\n=== RÉPARTITION UTILISATEURS PAR ORGANISATION ===\n";
$usersByOrg = DB::table('users')
    ->leftJoin('organization_user', 'users.id', '=', 'organization_user.user_id')
    ->selectRaw('organization_user.organization_id, COUNT(*) as count')
    ->groupBy('organization_user.organization_id')
    ->get();

foreach ($usersByOrg as $row) {
    $orgId = $row->organization_id ?? 'NULL';
    echo "Organisation {$orgId} : {$row->count} utilisateurs\n";
}

echo "\n=== FIN AUDIT ===\n";
