#!/bin/bash
# ========================================
# CORRECTION - MÉTHODE FETCHCASES MANQUANTE
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

header "CORRECTION - MÉTHODE FETCHCASES MANQUANTE"

echo "🔍 Erreur identifiée :"
echo "   Call to undefined method App\Services\CommCareService::fetchCases()"
echo "   Erreur 500 dans /onboarding/mapping/properties"
echo ""

echo "🎯 Cause :"
echo "   Méthode fetchCases() appelée mais n'existe pas"
echo "   Confusion entre fetchCaseTypes() et fetchCases()"
echo ""

echo "✅ Solution appliquée :"
echo "   Création de la méthode fetchMultipleCases()"
echo "   Basée sur fetchLatestCase() avec limite paramétrable"
echo "   Correction de l'appel dans extractAllProperties()"
echo ""

echo "📁 Modifications effectuées :"

echo ""
echo "   🔧 CommCareService.php :"
echo "      ✅ Nouvelle méthode fetchMultipleCases()"
echo "      ✅ Paramètre limit configurable (défaut: 10)"
echo "      ✅ Même logique que fetchLatestCase() mais avec plusieurs cases"
echo "      ✅ Correction de l'appel dans extractAllProperties()"

echo ""
echo "🛠️  Actions effectuées :"
echo "   1. Création de fetchMultipleCases()"
echo "   2. Correction de l'appel dans extractAllProperties()"
echo "   3. Clearing du cache Laravel"
echo ""

echo "📋 Méthode fetchMultipleCases() :"
echo "   - Récupère jusqu'à 10 cases d'un type spécifique"
echo "   - Utilise l'API CommCare /api/case/v1/"
echo "   - Filtre par case_type et closed=false"
echo "   - Gestion d'erreur robuste"
echo ""

echo "🎯 Avantages de cette solution :"
echo "   ✅ Méthode manquante créée"
echo "   ✅ Récupération de plusieurs cases"
echo "   ✅ Extraction complète des propriétés"
echo "   ✅ Fallback vers fetchLatestCase() si erreur"
echo ""

header "CORRECTION TERMINÉE"

echo "🎉 Erreur corrigée !"
echo ""
echo "🚀 Actions à effectuer côté navigateur :"
echo "   1. Vider le cache du navigateur (Ctrl+Shift+R ou Cmd+Shift+R)"
echo "   2. Recharger la page /onboarding/mapping"
echo "   3. Tester le chargement des propriétés avec 'woman'"
echo ""

echo "📝 Résultat attendu :"
echo "   ✅ Plus d'erreur 500"
echo "   ✅ Chargement des propriétés réussi"
echo "   ✅ Plus de propriétés trouvées (phone_number, consent_sms, etc.)"
echo "   ✅ Liste complète des propriétés disponibles"
echo ""

echo "🔧 Si problème persiste :"
echo "   1. DevTools → Console → Vérifier les erreurs"
echo "   2. DevTools → Network → Vérifier la requête POST"
echo "   3. Vérifier les logs Laravel : docker logs notify_sms_app"
echo ""

success "Correction terminée ! Testez maintenant le chargement des propriétés."

echo ""
echo "🧪 Test à effectuer :"
echo "   1. Aller sur /onboarding/mapping"
echo "   2. Saisir 'woman' dans le champ Case Type"
echo "   3. Cliquer sur 'Charger les propriétés'"
echo "   4. Vérifier que les propriétés se chargent sans erreur"
echo ""
echo "📊 Propriétés attendues :"
echo "   - phone_number"
echo "   - consent_sms"
echo "   - husband_phone_number"
echo "   - phone_number_search"
echo "   - Et toutes les autres propriétés personnalisées"
echo ""
