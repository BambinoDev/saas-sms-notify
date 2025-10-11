#!/bin/bash

# Script de migration vers Multi-Tenant SAAS
# Sprint 1 - Jour 1-2
# Date: 10 octobre 2025

set -e

echo "════════════════════════════════════════════════════"
echo "🚀 MIGRATION MULTI-TENANT - Sprint 1"
echo "════════════════════════════════════════════════════"
echo ""

# Vérifier que Docker tourne
echo "🔍 Vérification de Docker..."
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker n'est pas démarré !"
    echo "👉 Démarrez Docker avec : open -a Docker"
    echo "👉 Attendez que Docker soit prêt, puis relancez ce script."
    exit 1
fi
echo "✅ Docker est démarré"
echo ""

# Vérifier que le container tourne
echo "🔍 Vérification du container notify_sms_app..."
if ! docker ps | grep -q "notify_sms_app"; then
    echo "❌ Container notify_sms_app n'est pas démarré !"
    echo "👉 Démarrez les containers avec : docker-compose up -d"
    exit 1
fi
echo "✅ Container notify_sms_app est actif"
echo ""

# Exécuter les migrations
echo "📦 Exécution des migrations..."
echo "────────────────────────────────────────────────────"
docker exec notify_sms_app php artisan migrate
echo ""

# Exécuter le seeder
echo "🌱 Exécution du seeder (création organisation par défaut)..."
echo "────────────────────────────────────────────────────"
docker exec notify_sms_app php artisan db:seed --class=DefaultOrganizationSeeder
echo ""

# Validation
echo "════════════════════════════════════════════════════"
echo "✅ VALIDATION"
echo "════════════════════════════════════════════════════"
echo ""

echo "📊 Vérification de l'organisation..."
docker exec notify_sms_app php artisan tinker --execute="
\$org = \App\Models\Organization::first();
if (\$org) {
    echo '✅ Organisation: ' . \$org->name . '\n';
    echo '   Slug: ' . \$org->slug . '\n';
    echo '   Status: ' . \$org->status . '\n';
    echo '   Users: ' . \$org->users()->count() . '\n';
    echo '   Cases: ' . \$org->cases()->count() . '\n';
    echo '   SMS Queue: ' . \$org->smsQueue()->count() . '\n';
    echo '   Rules: ' . \$org->rules()->count() . '\n';
} else {
    echo '❌ Aucune organisation trouvée\n';
}
"
echo ""

echo "💳 Vérification de la subscription..."
docker exec notify_sms_app php artisan tinker --execute="
\$sub = \App\Models\Subscription::first();
if (\$sub) {
    echo '✅ Subscription:\n';
    echo '   Plan: ' . \$sub->plan . '\n';
    echo '   Status: ' . \$sub->status . '\n';
    echo '   SMS Limit: ' . number_format(\$sub->sms_limit) . '\n';
    echo '   SMS Used: ' . \$sub->sms_used . '\n';
    echo '   Price: ' . \$sub->price . ' €\n';
} else {
    echo '❌ Aucune subscription trouvée\n';
}
"
echo ""

echo "🔍 Vérification des données orphelines..."
docker exec notify_sms_app php artisan tinker --execute="
\$cases = \App\Models\CaseModel::whereNull('organization_id')->count();
\$sms = \App\Models\SmsQueue::whereNull('organization_id')->count();
\$rules = \App\Models\SmsRule::whereNull('organization_id')->count();

if (\$cases === 0 && \$sms === 0 && \$rules === 0) {
    echo '✅ Toutes les données sont liées à une organisation\n';
} else {
    echo '⚠️  Données orphelines trouvées:\n';
    if (\$cases > 0) echo '   - Cases: ' . \$cases . '\n';
    if (\$sms > 0) echo '   - SMS Queue: ' . \$sms . '\n';
    if (\$rules > 0) echo '   - Rules: ' . \$rules . '\n';
}
"
echo ""

echo "════════════════════════════════════════════════════"
echo "✨ MIGRATION TERMINÉE AVEC SUCCÈS !"
echo "════════════════════════════════════════════════════"
echo ""
echo "📚 Documentation complète : MIGRATION_MULTI_TENANT_SPRINT1.md"
echo ""
echo "🎯 Prochaines étapes :"
echo "   1. Créer Middleware OrganizationContext"
echo "   2. Ajouter Scopes Eloquent"
echo "   3. Créer pages Admin Organizations"
echo "   4. Créer pages Admin Subscriptions"
echo "   5. Écrire tests unitaires"
echo ""
echo "👉 Pour tester l'application : http://localhost:8000"
echo ""

