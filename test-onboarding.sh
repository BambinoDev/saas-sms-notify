#!/bin/bash
# ========================================
# SCRIPT HELPER POUR TESTS ONBOARDING
# CommCare SMS SAAS
# ========================================

set -e

GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

PROJECT_DIR="/Users/rodsid/SCI/SCI/DevProject/SAAS CommCare SMS"
CONTAINER="notify_sms_app"

function header() {
    echo -e "\n${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}\n"
}

function success() {
    echo -e "${GREEN}✅ $1${NC}"
}

function warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

function error() {
    echo -e "${RED}❌ $1${NC}"
}

function run_php() {
    docker exec "$CONTAINER" php -r "
require __DIR__.'/vendor/autoload.php';
\$app = require_once __DIR__.'/bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Utiliser l'organisation 'Ministère de la Santé - Côte d'Ivoire' (ID: 1)
// car c'est celle qui a été réinitialisée pour les tests
define('TEST_ORG_ID', 1);

$1
"
}

# ========================================
# COMMANDES DISPONIBLES
# ========================================

case "$1" in
    
    # ----------------------------------------
    # RESET ONBOARDING
    # ----------------------------------------
    reset)
        header "RESET ONBOARDING"
        echo "Réinitialisation de l'organisation..."
        
        run_php "
\$org = App\Models\Organization::find(TEST_ORG_ID);
if (\$org) {
    \$org->update([
        'onboarding_completed' => false,
        'onboarding_step' => 1,
        'onboarding_completed_at' => null,
        'organization_type' => null,
        'industry' => null,
        'timezone' => 'Africa/Abidjan',
        'team_size' => null,
        'project_description' => null,
        'commcare_email' => null,
        'commcare_api_key' => null,
        'commcare_project_space' => null,
        'commcare_app_id' => null,
        'commcare_project_name' => null,
        'commcare_case_type' => null,
        'case_properties_mapping' => null,
        'phone_number_field' => null,
        'eligibility_field' => null,
        'eligibility_condition' => null,
        'eligibility_value' => null,
    ]);
    echo '✅ Organisation réinitialisée' . PHP_EOL;
    echo 'Organization: ' . \$org->name . PHP_EOL;
    echo 'Step: ' . \$org->onboarding_step . '/5' . PHP_EOL;
} else {
    echo '❌ Aucune organisation trouvée' . PHP_EOL;
}
"
        ;;
    
    # ----------------------------------------
    # STATUS GÉNÉRAL
    # ----------------------------------------
    status)
        header "STATUS ONBOARDING"
        
        run_php "
\$org = App\Models\Organization::find(TEST_ORG_ID);
if (\$org) {
    echo '📋 Organisation: ' . \$org->name . PHP_EOL;
    echo '📊 Onboarding Step: ' . \$org->onboarding_step . '/5' . PHP_EOL;
    echo '✅ Completed: ' . (\$org->onboarding_completed ? 'YES' : 'NO') . PHP_EOL;
    
    if (\$org->onboarding_step >= 2) {
        echo PHP_EOL . '🏢 COMPANY INFO:' . PHP_EOL;
        echo '   Type: ' . (\$org->organization_type ?? '-') . PHP_EOL;
        echo '   Industry: ' . (\$org->industry ?? '-') . PHP_EOL;
        echo '   Timezone: ' . (\$org->timezone ?? '-') . PHP_EOL;
    }
    
    if (\$org->onboarding_step >= 3) {
        echo PHP_EOL . '🔗 COMMCARE:' . PHP_EOL;
        echo '   Email: ' . (\$org->commcare_email ?? '-') . PHP_EOL;
        echo '   Project Space: ' . (\$org->commcare_project_space ?? '-') . PHP_EOL;
        echo '   Project Name: ' . (\$org->commcare_project_name ?? '-') . PHP_EOL;
    }
    
    if (\$org->onboarding_step >= 4) {
        echo PHP_EOL . '📋 MAPPING:' . PHP_EOL;
        echo '   Case Type: ' . (\$org->commcare_case_type ?? '-') . PHP_EOL;
        echo '   Phone Field: ' . (\$org->phone_number_field ?? '-') . PHP_EOL;
        if (\$org->case_properties_mapping) {
            echo '   Properties: ' . count(\$org->case_properties_mapping) . ' selected' . PHP_EOL;
        }
    }
} else {
    echo '❌ Aucune organisation trouvée' . PHP_EOL;
}
"
        ;;
    
    # ----------------------------------------
    # CHECK ÉTAPE 2 : COMPANY
    # ----------------------------------------
    check-step2)
        header "VÉRIFICATION ÉTAPE 2 : COMPANY"
        
        run_php "
\$org = App\Models\Organization::find(TEST_ORG_ID);

echo '📋 Données Company:' . PHP_EOL;
echo '   Organization Type: ' . (\$org->organization_type ?? '❌ MANQUANT') . PHP_EOL;
echo '   Industry: ' . (\$org->industry ?? '❌ MANQUANT') . PHP_EOL;
echo '   Timezone: ' . (\$org->timezone ?? '❌ MANQUANT') . PHP_EOL;
echo '   Team Size: ' . (\$org->team_size ?? '❌ MANQUANT') . PHP_EOL;
echo '   Description: ' . (substr(\$org->project_description ?? '-', 0, 50)) . '...' . PHP_EOL;
echo '   Onboarding Step: ' . \$org->onboarding_step . PHP_EOL;

echo PHP_EOL;

\$errors = [];
if (!\$org->organization_type) \$errors[] = 'Organization type manquant';
if (!\$org->industry) \$errors[] = 'Industry manquant';
if (!\$org->timezone) \$errors[] = 'Timezone manquant';
if (!\$org->team_size) \$errors[] = 'Team size manquant';
if (!\$org->project_description) \$errors[] = 'Description manquante';
if (\$org->onboarding_step !== 3) \$errors[] = 'Step devrait être 3 (actuel: ' . \$org->onboarding_step . ')';

if (count(\$errors) === 0) {
    echo '✅ ÉTAPE 2 VALIDÉE' . PHP_EOL;
} else {
    echo '❌ ERREURS DÉTECTÉES:' . PHP_EOL;
    foreach (\$errors as \$error) {
        echo '   - ' . \$error . PHP_EOL;
    }
}
"
        ;;
    
    # ----------------------------------------
    # CHECK ÉTAPE 3 : COMMCARE
    # ----------------------------------------
    check-step3)
        header "VÉRIFICATION ÉTAPE 3 : COMMCARE"
        
        run_php "
\$org = App\Models\Organization::find(TEST_ORG_ID);

echo '🔗 Données CommCare:' . PHP_EOL;
echo '   Email: ' . (\$org->commcare_email ?? '❌ MANQUANT') . PHP_EOL;
echo '   Project Space: ' . (\$org->commcare_project_space ?? '❌ MANQUANT') . PHP_EOL;
echo '   Project Name: ' . (\$org->commcare_project_name ?? '❌ MANQUANT') . PHP_EOL;
echo '   App ID: ' . (\$org->commcare_app_id ?? '❌ MANQUANT') . PHP_EOL;

if (\$org->commcare_api_key) {
    try {
        \$decrypted = Illuminate\Support\Facades\Crypt::decryptString(\$org->commcare_api_key);
        echo '   API Key: [ENCRYPTED] ✅' . PHP_EOL;
        echo '   API Key (50 chars): ' . substr(\$decrypted, 0, 50) . '...' . PHP_EOL;
    } catch (Exception \$e) {
        echo '   API Key: ❌ ERREUR DÉCRYPTAGE' . PHP_EOL;
    }
} else {
    echo '   API Key: ❌ MANQUANTE' . PHP_EOL;
}

echo '   Onboarding Step: ' . \$org->onboarding_step . PHP_EOL;

echo PHP_EOL;

\$errors = [];
if (!\$org->commcare_email) \$errors[] = 'Email manquant';
if (!\$org->commcare_project_space) \$errors[] = 'Project space manquant';
if (!\$org->commcare_api_key) \$errors[] = 'API key manquante';
if (\$org->onboarding_step !== 4) \$errors[] = 'Step devrait être 4 (actuel: ' . \$org->onboarding_step . ')';

if (count(\$errors) === 0) {
    echo '✅ ÉTAPE 3 VALIDÉE' . PHP_EOL;
} else {
    echo '❌ ERREURS DÉTECTÉES:' . PHP_EOL;
    foreach (\$errors as \$error) {
        echo '   - ' . \$error . PHP_EOL;
    }
}
"
        ;;
    
    # ----------------------------------------
    # CHECK ÉTAPE 4 : MAPPING
    # ----------------------------------------
    check-step4)
        header "VÉRIFICATION ÉTAPE 4 : MAPPING"
        
        run_php "
\$org = App\Models\Organization::find(TEST_ORG_ID);

echo '📋 Données Mapping:' . PHP_EOL;
echo '   Case Type: ' . (\$org->commcare_case_type ?? '❌ MANQUANT') . PHP_EOL;
echo '   Phone Field: ' . (\$org->phone_number_field ?? '❌ MANQUANT') . PHP_EOL;

if (\$org->case_properties_mapping) {
    \$props = \$org->case_properties_mapping;
    echo '   Properties Count: ' . count(\$props) . PHP_EOL;
    echo '   Properties: ' . implode(', ', array_slice(\$props, 0, 10)) . PHP_EOL;
} else {
    echo '   Properties: ❌ MANQUANTES' . PHP_EOL;
}

if (\$org->eligibility_field) {
    echo '   Eligibility Field: ' . \$org->eligibility_field . PHP_EOL;
    echo '   Eligibility Condition: ' . \$org->eligibility_condition . PHP_EOL;
    echo '   Eligibility Value: ' . \$org->eligibility_value . PHP_EOL;
} else {
    echo '   Eligibility: (non configuré)' . PHP_EOL;
}

echo '   Onboarding Step: ' . \$org->onboarding_step . PHP_EOL;

echo PHP_EOL;

\$errors = [];
if (!\$org->commcare_case_type) \$errors[] = 'Case type manquant';
if (!\$org->phone_number_field) \$errors[] = 'Phone field manquant';
if (!\$org->case_properties_mapping || count(\$org->case_properties_mapping) === 0) \$errors[] = 'Properties manquantes';
if (\$org->onboarding_step !== 5) \$errors[] = 'Step devrait être 5 (actuel: ' . \$org->onboarding_step . ')';

if (count(\$errors) === 0) {
    echo '✅ ÉTAPE 4 VALIDÉE' . PHP_EOL;
} else {
    echo '❌ ERREURS DÉTECTÉES:' . PHP_EOL;
    foreach (\$errors as \$error) {
        echo '   - ' . \$error . PHP_EOL;
    }
}
"
        ;;
    
    # ----------------------------------------
    # CHECK FINAL
    # ----------------------------------------
    check-final)
        header "VÉRIFICATION FINALE COMPLÈTE"
        
        run_php "
\$org = App\Models\Organization::find(TEST_ORG_ID);

echo '========================================' . PHP_EOL;
echo '   CONFIGURATION FINALE COMPLÈTE      ' . PHP_EOL;
echo '========================================' . PHP_EOL . PHP_EOL;

echo '📋 COMPANY' . PHP_EOL;
echo '  Type: ' . (\$org->organization_type ?? '❌') . PHP_EOL;
echo '  Industry: ' . (\$org->industry ?? '❌') . PHP_EOL;
echo '  Timezone: ' . (\$org->timezone ?? '❌') . PHP_EOL;
echo '  Team Size: ' . (\$org->team_size ?? '❌') . PHP_EOL;
echo '  Description: ' . (substr(\$org->project_description ?? '-', 0, 50)) . '...' . PHP_EOL . PHP_EOL;

echo '🔗 COMMCARE' . PHP_EOL;
echo '  Email: ' . (\$org->commcare_email ?? '❌') . PHP_EOL;
echo '  Project Space: ' . (\$org->commcare_project_space ?? '❌') . PHP_EOL;
echo '  Project Name: ' . (\$org->commcare_project_name ?? '❌') . PHP_EOL;
echo '  App ID: ' . (\$org->commcare_app_id ?? '❌') . PHP_EOL;
echo '  API Key: [ENCRYPTED]' . PHP_EOL . PHP_EOL;

echo '📊 MAPPING' . PHP_EOL;
echo '  Case Type: ' . (\$org->commcare_case_type ?? '❌') . PHP_EOL;
if (\$org->case_properties_mapping) {
    echo '  Properties: ' . count(\$org->case_properties_mapping) . ' selected' . PHP_EOL;
} else {
    echo '  Properties: ❌' . PHP_EOL;
}
echo '  Phone Field: ' . (\$org->phone_number_field ?? '❌') . PHP_EOL;

if (\$org->eligibility_field) {
    echo '  Eligibility: ' . \$org->eligibility_field . ' ' . \$org->eligibility_condition . ' ' . \$org->eligibility_value . PHP_EOL;
}

echo PHP_EOL . '✅ ONBOARDING' . PHP_EOL;
echo '  Completed: ' . (\$org->onboarding_completed ? 'YES ✅' : 'NO ❌') . PHP_EOL;
echo '  Step: ' . \$org->onboarding_step . '/5' . PHP_EOL;
echo '  Completed At: ' . (\$org->onboarding_completed_at ?? '-') . PHP_EOL . PHP_EOL;

// VALIDATION FINALE
\$errors = [];

if (!\$org->organization_type) \$errors[] = 'Company info manquante';
if (!\$org->commcare_email) \$errors[] = 'CommCare config manquante';
if (!\$org->commcare_case_type) \$errors[] = 'Mapping manquant';
if (!\$org->phone_number_field) \$errors[] = 'Champ téléphone manquant';
if (!\$org->onboarding_completed) \$errors[] = 'Onboarding non marqué terminé';
if (\$org->onboarding_step !== 5) \$errors[] = 'Step incorrect (devrait être 5)';

if (count(\$errors) === 0) {
    echo '========================================' . PHP_EOL;
    echo '   🎉 ONBOARDING 100% VALIDÉ ✅ 🎉      ' . PHP_EOL;
    echo '========================================' . PHP_EOL;
} else {
    echo '========================================' . PHP_EOL;
    echo '   ❌ ERREURS DÉTECTÉES                 ' . PHP_EOL;
    echo '========================================' . PHP_EOL;
    foreach (\$errors as \$error) {
        echo '  ❌ ' . \$error . PHP_EOL;
    }
}
"
        ;;
    
    # ----------------------------------------
    # SET STEP (pour debugging)
    # ----------------------------------------
    set-step)
        if [ -z "$2" ]; then
            error "Usage: $0 set-step <1-5>"
            exit 1
        fi
        
        header "FORCER ONBOARDING STEP"
        echo "Changement du step vers $2..."
        
        run_php "
\$org = App\Models\Organization::find(TEST_ORG_ID);
if (\$org) {
    \$org->update(['onboarding_step' => $2]);
    echo '✅ Onboarding step changé: ' . \$org->onboarding_step . PHP_EOL;
} else {
    echo '❌ Aucune organisation trouvée' . PHP_EOL;
}
"
        ;;
    
    # ----------------------------------------
    # CHECK ORG
    # ----------------------------------------
    check-org)
        header "VÉRIFICATION ORGANISATION"
        
        run_php "
\$user = App\Models\User::first();
if (!\$user) {
    echo '❌ Aucun utilisateur trouvé' . PHP_EOL;
    exit(1);
}

echo '👤 User: ' . \$user->email . PHP_EOL;

\$org = \$user->organizations()->first();
if (!\$org) {
    echo '❌ Utilisateur n\'a pas d\'organisation' . PHP_EOL;
    exit(1);
}

echo '🏢 Organization: ' . \$org->name . PHP_EOL;
echo '✅ L\'utilisateur a une organisation' . PHP_EOL;
"
        ;;
    
    # ----------------------------------------
    # LOGS
    # ----------------------------------------
    logs)
        header "LOGS TEMPS RÉEL"
        echo "Appuyez sur Ctrl+C pour arrêter..."
        echo ""
        tail -f "$PROJECT_DIR/storage/logs/laravel.log" | grep --color=always -E "(Onboarding|CommCare|ERROR|CRITICAL|WARNING|^)"
        ;;
    
    # ----------------------------------------
    # HELP
    # ----------------------------------------
    *)
        header "HELPER TESTS ONBOARDING"
        echo "Usage: $0 <commande>"
        echo ""
        echo "Commandes disponibles:"
        echo ""
        echo "  ${GREEN}reset${NC}           Réinitialiser l'onboarding (step 1)"
        echo "  ${GREEN}status${NC}          Afficher l'état actuel de l'onboarding"
        echo ""
        echo "  ${BLUE}check-step2${NC}     Vérifier les données de l'étape 2 (Company)"
        echo "  ${BLUE}check-step3${NC}     Vérifier les données de l'étape 3 (CommCare)"
        echo "  ${BLUE}check-step4${NC}     Vérifier les données de l'étape 4 (Mapping)"
        echo "  ${BLUE}check-final${NC}     Vérification finale complète"
        echo ""
        echo "  ${YELLOW}set-step <1-5>${NC}  Forcer un step spécifique (debugging)"
        echo "  ${YELLOW}check-org${NC}       Vérifier l'existence de l'organisation"
        echo "  ${YELLOW}logs${NC}            Afficher les logs en temps réel"
        echo ""
        echo "Exemples:"
        echo "  $0 reset"
        echo "  $0 status"
        echo "  $0 check-step2"
        echo "  $0 check-final"
        echo ""
        ;;
esac

