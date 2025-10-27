# 🚀 INSTRUCTIONS RAPIDES
## Migration Multi-Tenant - Sprint 1 Jour 1-2

---

## ⚡ TL;DR - 3 Commandes

```bash
# 1. Démarrer Docker
open -a Docker

# 2. Exécuter le setup
cd "/Users/rodsid/SCI/SCI/DevProject/SAAS CommCare SMS"
chmod +x setup-multi-tenant.sh
./setup-multi-tenant.sh

# 3. Tester l'application
open http://localhost:8000
```

**C'est tout !** ✨

---

## 📋 Ce qui a été créé

✅ **4 migrations** → Tables organizations, subscriptions, organization_user  
✅ **2 nouveaux modèles** → Organization.php, Subscription.php  
✅ **4 modèles modifiés** → User.php, CaseModel.php, SmsQueue.php, SmsRule.php  
✅ **1 seeder** → DefaultOrganizationSeeder.php  
✅ **1 script automatisé** → setup-multi-tenant.sh  
✅ **3 docs complètes** → MIGRATION_MULTI_TENANT_SPRINT1.md, RESUME_SPRINT1_J1-2.md, SPRINT1_STATUS.md

---

## 🎯 Résultat attendu

Après exécution du script :

```
✨ MIGRATION TERMINÉE AVEC SUCCÈS !

Organisation : Ministère de la Santé - Côte d'Ivoire
Plan : Enterprise
SMS Limit : 999,999 / mois
Users : [nombre] attachés
Cases : [nombre] migrés
SMS Queue : [nombre] migrés
Rules : [nombre] migrées
```

---

## 📚 Documentation complète

- **Technique :** `MIGRATION_MULTI_TENANT_SPRINT1.md`
- **Résumé :** `RESUME_SPRINT1_J1-2.md`
- **Status :** `SPRINT1_STATUS.md`
- **Ce fichier :** Instructions rapides

---

## ❓ En cas de problème

### Docker ne démarre pas ?
```bash
# Vérifier Docker Desktop est installé
open -a Docker
# Attendre que l'icône Docker soit stable
```

### Container pas trouvé ?
```bash
# Démarrer les containers
docker-compose up -d

# Vérifier
docker ps
```

### Erreur pendant migration ?
```bash
# Rollback
docker exec notify_sms_app php artisan migrate:rollback --step=4
```

---

## 📞 Aide

Consultez `MIGRATION_MULTI_TENANT_SPRINT1.md` pour :
- Documentation détaillée
- Commandes de validation
- Tests à exécuter
- Architecture complète

---

**🎉 Bonne migration !**

