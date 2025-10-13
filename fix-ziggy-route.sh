#!/bin/bash
# ========================================
# SCRIPT DE CORRECTION - ERREUR ZIGGY ROUTE
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

header "CORRECTION ERREUR ZIGGY ROUTE - onboarding.commcare.test"

echo "🔍 Diagnostic de l'erreur :"
echo "   Route 'onboarding.commcare.test' is not defined"
echo "   Message d'erreur: The route onboarding/null could not be found"
echo ""

# Étape 1 : Vérifier les routes Laravel
echo "1️⃣ Vérification des routes Laravel..."
if docker exec "$CONTAINER" php artisan route:list --path=onboarding | grep -q "onboarding.commcare.test"; then
    success "Route Laravel 'onboarding.commcare.test' trouvée"
else
    error "Route Laravel 'onboarding.commcare.test' NON trouvée"
    exit 1
fi

# Étape 2 : Vérifier le fichier Ziggy
echo ""
echo "2️⃣ Vérification du fichier Ziggy..."
if docker exec "$CONTAINER" php artisan ziggy:generate; then
    success "Fichier Ziggy régénéré"
else
    error "Échec de la régénération Ziggy"
    exit 1
fi

# Étape 3 : Vérifier le contenu du fichier Ziggy
echo ""
echo "3️⃣ Vérification du contenu Ziggy..."
if grep -q "onboarding.commcare.test" resources/js/ziggy.js; then
    success "Route 'onboarding.commcare.test' présente dans ziggy.js"
else
    error "Route 'onboarding.commcare.test' ABSENTE de ziggy.js"
    echo "Contenu du fichier ziggy.js :"
    cat resources/js/ziggy.js | head -5
    exit 1
fi

# Étape 4 : Clearing cache Laravel
echo ""
echo "4️⃣ Clearing cache Laravel..."
docker exec "$CONTAINER" php artisan cache:clear
docker exec "$CONTAINER" php artisan config:clear
docker exec "$CONTAINER" php artisan route:clear
docker exec "$CONTAINER" php artisan view:clear
success "Cache Laravel vidé"

# Étape 5 : Rebuild frontend
echo ""
echo "5️⃣ Rebuild frontend..."
if docker exec notify_sms_node npm run build; then
    success "Frontend rebuildé avec succès"
else
    error "Échec du rebuild frontend"
    exit 1
fi

# Étape 6 : Test de l'endpoint backend
echo ""
echo "6️⃣ Test de l'endpoint backend..."
if docker exec "$CONTAINER" php -r "
require __DIR__.'/vendor/autoload.php';
\$app = require_once __DIR__.'/bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    \$response = app('App\Http\Controllers\OnboardingController')->testCommcare(
        new Illuminate\Http\Request([
            'email' => 'lucas.sidibe@savethechildren.org',
            'api_key' => 'f976f7136f793ff1989aed85529fd673ecddb696',
            'project_space' => 'sci-civ-malaria'
        ])
    );
    echo 'Status: ' . \$response->getStatusCode() . PHP_EOL;
    if (\$response->getStatusCode() === 200) {
        echo 'SUCCESS: Endpoint fonctionne' . PHP_EOL;
        exit(0);
    } else {
        echo 'ERROR: Status code non-200' . PHP_EOL;
        exit(1);
    }
} catch (Exception \$e) {
    echo 'ERROR: ' . \$e->getMessage() . PHP_EOL;
    exit(1);
}
" | grep -q "SUCCESS"; then
    success "Endpoint backend fonctionne"
else
    error "Endpoint backend ne fonctionne pas"
    exit 1
fi

# Étape 7 : Vérification finale
echo ""
echo "7️⃣ Vérification finale..."
echo "   ✅ Route Laravel configurée"
echo "   ✅ Fichier Ziggy régénéré"
echo "   ✅ Cache Laravel vidé"
echo "   ✅ Frontend rebuildé"
echo "   ✅ Endpoint backend testé"
echo ""

header "CORRECTION TERMINÉE"

echo "🎯 Solutions appliquées :"
echo "   1. Régénération du fichier Ziggy"
echo "   2. Vidage du cache Laravel"
echo "   3. Rebuild du frontend"
echo "   4. Test de l'endpoint backend"
echo ""

echo "📋 Actions à effectuer côté navigateur :"
echo "   1. Vider le cache du navigateur (Ctrl+Shift+R ou Cmd+Shift+R)"
echo "   2. Ouvrir DevTools (F12)"
echo "   3. Aller dans l'onglet Network"
echo "   4. Cocher 'Disable cache'"
echo "   5. Recharger la page"
echo ""

echo "🔧 Si le problème persiste :"
echo "   1. Ouvrir DevTools → Console"
echo "   2. Vérifier les erreurs JavaScript"
echo "   3. Aller dans DevTools → Application → Storage → Clear storage"
echo "   4. Recharger la page"
echo ""

echo "📁 Fichier de debug créé : debug-ziggy-route.html"
echo "   Ouvrir ce fichier dans le navigateur pour diagnostiquer Ziggy"
echo ""

success "Correction terminée ! Testez maintenant dans le navigateur."

echo ""
echo "🚀 URL à tester :"
echo "   http://localhost:8080/onboarding/commcare"
echo ""
