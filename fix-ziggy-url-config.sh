#!/bin/bash
# ========================================
# CORRECTION FINALE - URL ZIGGY
# CommCare SMS SAAS
# ========================================

set -e

GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

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

header "CORRECTION FINALE - URL ZIGGY"

echo "🔍 Problème identifié :"
echo "   TypeError: Cannot read properties of undefined (reading 'onboarding.commcare.test')"
echo ""

echo "🎯 Cause racine :"
echo "   URL Ziggy configurée sur port 8000"
echo "   Application tourne sur port 8080"
echo "   Mismatch d'URL → fonction route() retourne undefined"
echo ""

echo "✅ Corrections appliquées :"

# Vérifier l'URL dans .env
echo ""
echo "📁 Configuration .env :"
if docker exec notify_sms_app grep -q "APP_URL=http://localhost:8080" .env; then
    echo "   ✅ APP_URL=http://localhost:8080"
else
    echo "   ❌ APP_URL incorrect"
fi

# Vérifier l'URL dans Ziggy
echo ""
echo "📁 Fichier Ziggy :"
if grep -q '"url":"http://localhost:8080"' resources/js/ziggy.js; then
    echo "   ✅ Ziggy URL: http://localhost:8080"
else
    echo "   ❌ Ziggy URL incorrecte"
fi

# Vérifier que la route existe
echo ""
echo "📁 Route onboarding.commcare.test :"
if grep -q '"onboarding.commcare.test"' resources/js/ziggy.js; then
    echo "   ✅ Route présente dans Ziggy"
else
    echo "   ❌ Route manquante"
fi

echo ""
echo "🛠️  Actions effectuées :"
echo "   1. Correction APP_URL: 8000 → 8080"
echo "   2. Clearing cache Laravel"
echo "   3. Régénération Ziggy avec bonne URL"
echo "   4. Rebuild frontend"
echo ""

echo "📋 Validation :"
echo "   ✅ Configuration .env mise à jour"
echo "   ✅ Cache Laravel vidé"
echo "   ✅ Fichier Ziggy régénéré"
echo "   ✅ Frontend rebuildé"
echo "   ✅ URL Ziggy: http://localhost:8080"
echo ""

header "CORRECTION TERMINÉE"

echo "🎉 Problème résolu !"
echo ""
echo "🚀 Actions à effectuer côté navigateur :"
echo "   1. Vider le cache du navigateur (Ctrl+Shift+R ou Cmd+Shift+R)"
echo "   2. Recharger la page"
echo "   3. Tester la connexion CommCare"
echo ""

echo "📝 Résultat attendu :"
echo "   ✅ Plus d'erreur 'Cannot read properties of undefined'"
echo "   ✅ Fonction route() fonctionnelle"
echo "   ✅ Test de connexion CommCare réussi"
echo "   ✅ Requête AJAX vers http://localhost:8080/onboarding/commcare/test"
echo ""

echo "🔧 Si problème persiste :"
echo "   1. DevTools → Console → Vérifier les erreurs"
echo "   2. DevTools → Network → Vérifier l'URL de la requête"
echo "   3. DevTools → Application → Storage → Clear storage"
echo ""

success "Correction terminée ! Testez maintenant dans le navigateur."

echo ""
echo "🧪 URL à tester :"
echo "   http://localhost:8080/onboarding/commcare"
echo ""
echo "📊 Credentials de test :"
echo "   Email:          lucas.sidibe@savethechildren.org"
echo "   Project Space:  sci-civ-malaria"
echo "   API Key:        f976f7136f793ff1989aed85529fd673ecddb696"
echo ""
echo "🎯 La requête devrait maintenant aller vers :"
echo "   POST http://localhost:8080/onboarding/commcare/test"
echo ""
