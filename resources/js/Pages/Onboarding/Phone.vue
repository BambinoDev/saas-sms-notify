<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import OnboardingLayout from '@/Layouts/OnboardingLayout.vue';
import Button from '@/Components/ui/Button.vue';
import { ref, watch } from 'vue';

const form = useForm({
  country_iso: 'CI',
  country_code: '+225',
  national_length: 10,
  validation_mode: 'strict',
  mobile_only: true,
  auto_format_e164: true,
  allowed_prefixes: ['01', '05', '07'],
  blocked_prefixes: [],
});

// Presets par pays (depuis la BDD)
const countryPresets = {
  'CI': { code: '+225', length: 10, prefixes: ['01', '05', '07'] },
  'GH': { code: '+233', length: 9, prefixes: ['02', '05'] },
  'NG': { code: '+234', length: 10, prefixes: ['07', '08', '09'] },
  'SN': { code: '+221', length: 9, prefixes: ['77', '78', '76', '70'] },
  'ML': { code: '+223', length: 8, prefixes: ['07', '09'] },
  'BF': { code: '+226', length: 8, prefixes: ['07'] },
  'CM': { code: '+237', length: 9, prefixes: ['6'] },
  'TG': { code: '+228', length: 8, prefixes: ['90', '91', '92', '93'] },
  'BJ': { code: '+229', length: 8, prefixes: ['01', '96', '97'] },
};

watch(() => form.country_iso, (newCountry) => {
  const preset = countryPresets[newCountry];
  if (preset) {
    form.country_code = preset.code;
    form.national_length = preset.length;
    form.allowed_prefixes = preset.prefixes;
  }
});

const togglePrefix = (prefix) => {
  const index = form.allowed_prefixes.indexOf(prefix);
  if (index > -1) {
    form.allowed_prefixes.splice(index, 1);
  } else {
    form.allowed_prefixes.push(prefix);
  }
};

const nextStep = () => {
  form.post('/onboarding/phone', {
    onSuccess: () => {
      router.visit('/onboarding/mapping');
    },
  });
};

const previousStep = () => {
  router.visit('/onboarding/commcare');
};
</script>

<template>
  <Head title="Configuration Téléphonie - S-Remind" />

  <OnboardingLayout :current-step="4">
    
    <div class="space-y-8 animate-fade-in">
      
      <!-- Header -->
      <div>
        <h2 class="text-3xl font-bold text-dark-900">
          Configurez la validation des numéros
        </h2>
        <p class="text-dark-600 mt-2">
          Définissez les règles de validation pour votre pays
        </p>
      </div>

      <!-- Form -->
      <form @submit.prevent="nextStep" class="space-y-6">
        
        <!-- Country Selection -->
        <div>
          <label for="country_iso" class="block text-sm font-medium text-dark-700 mb-2">
            Pays principal
          </label>
          <select
            id="country_iso"
            v-model="form.country_iso"
            required
            class="w-full px-4 py-3 border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all hover:border-dark-300"
          >
            <option value="CI">🇨🇮 Côte d'Ivoire (+225)</option>
            <option value="GH">🇬🇭 Ghana (+233)</option>
            <option value="NG">🇳🇬 Nigeria (+234)</option>
            <option value="SN">🇸🇳 Sénégal (+221)</option>
            <option value="ML">🇲🇱 Mali (+223)</option>
            <option value="BF">🇧🇫 Burkina Faso (+226)</option>
            <option value="CM">🇨🇲 Cameroun (+237)</option>
            <option value="TG">🇹🇬 Togo (+228)</option>
            <option value="BJ">🇧🇯 Bénin (+229)</option>
          </select>
          <p class="mt-2 text-sm text-dark-500">
            Les paramètres seront automatiquement adaptés à ce pays
          </p>
        </div>

        <!-- Phone Format Preview -->
        <div class="bg-primary-50 border border-primary-200 rounded-lg p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-primary-900">Format validé</p>
              <p class="text-lg font-mono text-primary-700 mt-1">
                {{ form.country_code }} {{ '·'.repeat(form.national_length) }}
              </p>
            </div>
            <div class="text-right">
              <p class="text-sm text-primary-700">Longueur nationale</p>
              <p class="text-2xl font-bold text-primary-900">{{ form.national_length }} chiffres</p>
            </div>
          </div>
        </div>

        <!-- Allowed Prefixes -->
        <div>
          <label class="block text-sm font-medium text-dark-700 mb-3">
            Préfixes autorisés
          </label>
          <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
            <button
              v-for="prefix in countryPresets[form.country_iso]?.prefixes || []"
              :key="prefix"
              type="button"
              @click="togglePrefix(prefix)"
              :class="[
                'px-4 py-3 border-2 rounded-lg font-mono font-semibold transition-all',
                form.allowed_prefixes.includes(prefix)
                  ? 'border-success-500 bg-success-50 text-success-700'
                  : 'border-dark-200 text-dark-500 hover:border-dark-300'
              ]"
            >
              {{ prefix }}
              <svg 
                v-if="form.allowed_prefixes.includes(prefix)" 
                class="w-4 h-4 inline-block ml-1" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </button>
          </div>
          <p class="mt-2 text-sm text-dark-500">
            Sélectionnez les préfixes valides pour votre pays
          </p>
        </div>

        <!-- Validation Mode -->
        <div>
          <label class="block text-sm font-medium text-dark-700 mb-3">
            Mode de validation
          </label>
          <div class="space-y-3">
            <label class="relative flex items-start p-4 border-2 rounded-lg cursor-pointer transition-all hover:border-primary-300 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50">
              <input
                type="radio"
                value="strict"
                v-model="form.validation_mode"
                class="mt-1 h-4 w-4 text-primary-600 focus:ring-primary-500"
              />
              <div class="ml-3">
                <p class="font-medium text-dark-900">Strict (Recommandé)</p>
                <p class="text-sm text-dark-600 mt-0.5">
                  Valide uniquement les numéros avec préfixes autorisés et longueur exacte
                </p>
              </div>
            </label>

            <label class="relative flex items-start p-4 border-2 rounded-lg cursor-pointer transition-all hover:border-primary-300 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50">
              <input
                type="radio"
                value="lenient"
                v-model="form.validation_mode"
                class="mt-1 h-4 w-4 text-primary-600 focus:ring-primary-500"
              />
              <div class="ml-3">
                <p class="font-medium text-dark-900">Souple</p>
                <p class="text-sm text-dark-600 mt-0.5">
                  Accepte les numéros même sans préfixes stricts (longueur uniquement)
                </p>
              </div>
            </label>
          </div>
        </div>

        <!-- Additional Options -->
        <div class="space-y-4">
          <label class="flex items-start space-x-3 p-4 bg-dark-50 rounded-lg cursor-pointer hover:bg-dark-100 transition-colors">
            <input
              type="checkbox"
              v-model="form.mobile_only"
              class="mt-1 h-5 w-5 text-primary-600 focus:ring-primary-500 border-dark-300 rounded"
            />
            <div class="flex-1">
              <p class="font-medium text-dark-900">Mobiles uniquement</p>
              <p class="text-sm text-dark-600 mt-0.5">
                Rejeter les numéros fixes et accepter uniquement les mobiles
              </p>
            </div>
          </label>

          <label class="flex items-start space-x-3 p-4 bg-dark-50 rounded-lg cursor-pointer hover:bg-dark-100 transition-colors">
            <input
              type="checkbox"
              v-model="form.auto_format_e164"
              class="mt-1 h-5 w-5 text-primary-600 focus:ring-primary-500 border-dark-300 rounded"
            />
            <div class="flex-1">
              <p class="font-medium text-dark-900">Formatage automatique E.164</p>
              <p class="text-sm text-dark-600 mt-0.5">
                Convertir automatiquement les numéros au format international
              </p>
            </div>
          </label>
        </div>

        <!-- Example Box -->
        <div class="bg-success-50 border border-success-200 rounded-lg p-4">
          <div class="flex items-start">
            <svg class="w-5 h-5 text-success-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="ml-3">
              <p class="text-sm font-medium text-success-900">Exemple de numéro valide</p>
              <p class="text-sm font-mono text-success-700 mt-1">
                {{ form.country_code }}{{ form.allowed_prefixes[0] || '07' }}{{ '1234567'.slice(0, form.national_length - 2) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex items-center justify-between pt-6 border-t border-dark-100">
          <Button
            type="button"
            variant="ghost"
            @click="previousStep"
            class="group"
          >
            <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Retour
          </Button>

          <Button
            type="submit"
            variant="primary"
            :disabled="form.processing || form.allowed_prefixes.length === 0"
            class="group"
          >
            <span v-if="!form.processing">
              Continuer
              <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
            </span>
            <span v-else class="flex items-center">
              <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Enregistrement...
            </span>
          </Button>
        </div>

      </form>

    </div>

  </OnboardingLayout>
</template>
