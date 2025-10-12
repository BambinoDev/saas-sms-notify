# CORRECTION SIGNUP ROUTE - 12 OCTOBRE 2025

## 🎯 PROBLÈME IDENTIFIÉ

L'erreur `Uncaught ReferenceError: route is not defined` dans `signup.vue` ligne 37 était causée par **deux problèmes** :

### 1. **Import manquant dans Signup.vue**
```javascript
// ❌ AVANT - Import manquant
import { Head, useForm } from '@inertiajs/vue3';
import Button from '@/Components/ui/Button.vue';
import { ref, computed } from 'vue';

// ✅ APRÈS - Import ajouté
import { Head, useForm } from '@inertiajs/vue3';
import Button from '@/Components/ui/Button.vue';
import { ref, computed } from 'vue';
import { route } from 'ziggy-js'; // ← AJOUTÉ
```

### 2. **Incohérence des noms de champs entre frontend et backend**

**Frontend (Signup.vue) :**
```javascript
const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  company_name: '',        // ← company_name
  country_iso: 'CI',       // ← country_iso  
  agree_terms: false,      // ← agree_terms
});
```

**Backend (SignupController.php) :**
```php
// ❌ AVANT - Noms incorrects
$validated = $request->validate([
    'organization_name' => ['required', 'string', 'max:255'], // ← organization_name
    'country' => ['required', 'string', 'size:2'],            // ← country
    'accept_terms' => ['required', 'accepted'],               // ← accept_terms
]);

// ✅ APRÈS - Noms corrigés
$validated = $request->validate([
    'company_name' => ['required', 'string', 'max:255'],      // ← company_name
    'country_iso' => ['required', 'string', 'size:2'],        // ← country_iso
    'agree_terms' => ['required', 'accepted'],               // ← agree_terms
]);
```

### 3. **Import ZiggyVue incorrect dans app.js**

```javascript
// ❌ AVANT - Chemin incorrect
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// ✅ APRÈS - Chemin corrigé
import { ZiggyVue } from 'ziggy-js';
```

---

## 🔧 CORRECTIONS APPLIQUÉES

### Fichier 1: `resources/js/Pages/Auth/Signup.vue`
```javascript
// Ligne 5 - Import ajouté
import { route } from 'ziggy-js';
```

### Fichier 2: `app/Http/Controllers/SignupController.php`
```php
// Lignes 29-31 - Noms de champs corrigés
'company_name' => ['required', 'string', 'max:255'],
'country_iso' => ['required', 'string', 'size:2'],
'agree_terms' => ['required', 'accepted'],

// Lignes 36-37 - Messages d'erreur corrigés
'agree_terms.accepted' => 'Vous devez accepter les conditions d\'utilisation.',
'country_iso.size' => 'Code pays invalide.',

// Lignes 49, 53, 56, 66-68 - Utilisation des bons noms
'locale' => $this->getLocaleForCountry($validated['country_iso']),
$slug = $this->generateUniqueSlug($validated['company_name']);
'name' => $validated['company_name'],
'primary_country' => $validated['country_iso'],
'timezone' => $this->getTimezoneForCountry($validated['country_iso']),
'allowed_prefixes' => $this->getDefaultPrefixesForCountry($validated['country_iso']),
```

### Fichier 3: `resources/js/app.js`
```javascript
// Ligne 6 - Import corrigé
import { ZiggyVue } from 'ziggy-js';
```

---

## ✅ VALIDATION

### Tests à effectuer :

1. **Test du helper route() :**
```javascript
// Dans la console du navigateur
console.log(typeof route); // doit retourner 'function'
console.log(route('signup')); // doit retourner '/signup'
console.log(route('signup.store')); // doit retourner '/signup'
```

2. **Test du formulaire signup :**
- Aller sur `http://localhost:8080/signup`
- Remplir le formulaire
- Cliquer sur "Créer mon compte"
- ✅ **Plus d'erreur `route is not defined`**

3. **Test de création de compte :**
- Vérifier que l'utilisateur est créé
- Vérifier que l'organisation est créée
- Vérifier que la subscription trial est créée
- Vérifier la redirection vers `/onboarding/welcome`

---

## 🚀 PROCHAINES ÉTAPES

Une fois cette correction validée :

1. **Créer les pages d'onboarding manquantes :**
   - `resources/js/Pages/Onboarding/Welcome.vue`
   - `resources/js/Pages/Onboarding/Company.vue`
   - `resources/js/Pages/Onboarding/CommCare.vue`
   - `resources/js/Pages/Onboarding/Phone.vue`
   - `resources/js/Pages/Onboarding/Mapping.vue`
   - `resources/js/Pages/Onboarding/Completion.vue`

2. **Implémenter les controllers d'onboarding :**
   - `OnboardingController` avec méthodes pour chaque étape
   - Sauvegarde progressive des données
   - Validation des credentials CommCare

3. **Finaliser le flow complet :**
   - Signup → Onboarding → Dashboard

---

## 📝 NOTES

- **Ziggy est configuré** : `tightenco/ziggy` v2.6.0 installé
- **Routes existent** : `signup` et `signup.store` définies
- **SignupController fonctionnel** : Création user + org + subscription
- **Problème Node.js** : npm non disponible dans le conteneur (à résoudre pour build)

**Le helper `route()` devrait maintenant fonctionner correctement !** ✨
