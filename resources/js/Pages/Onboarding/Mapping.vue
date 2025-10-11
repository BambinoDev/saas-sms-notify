<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import OnboardingLayout from '@/Layouts/OnboardingLayout.vue';
import Button from '@/Components/ui/Button.vue';
import { ref } from 'vue';

// Variables SMS disponibles
const smsVariables = [
  { 
    key: 'case_name', 
    label: 'Nom du bénéficiaire', 
    description: 'Nom complet de la personne',
    example: 'Marie KOUASSI'
  },
  { 
    key: 'visit_number', 
    label: 'Numéro de visite', 
    description: 'Numéro de la consultation (CPN1, CPN2...)',
    example: 'CPN3'
  },
  { 
    key: 'next_visit_date', 
    label: 'Date du prochain RDV', 
    description: 'Date formatée du rendez-vous',
    example: '15/03/2025'
  },
  { 
    key: 'facility_name', 
    label: 'Nom de la structure', 
    description: 'Centre de santé ou hôpital',
    example: 'CHU Cocody'
  },
  { 
    key: 'health_worker', 
    label: 'Agent de santé', 
    description: 'Nom du professionnel',
    example: 'Dr. DIALLO'
  },
];

const form = useForm({
  mappings: {
    case_name: 'name',
    visit_number: 'visit_type',
    next_visit_date: 'appointment_date',
    facility_name: 'facility',
    health_worker: 'case_owner',
  },
  custom_mappings: [],
});

const addCustomMapping = () => {
  form.custom_mappings.push({
    sms_variable: '',
    commcare_field: '',
  });
};

const removeCustomMapping = (index) => {
  form.custom_mappings.splice(index, 1);
};

const nextStep = () => {
  form.post('/onboarding/mapping', {
    onSuccess: () => {
      router.visit('/onboarding/completion');
    },
  });
};

const previousStep = () => {
  router.visit('/onboarding/phone');
};
</script>

<template>
  <Head title="Mapping des Champs - S-Remind" />

  <OnboardingLayout :current-step="5">
    
    <div class="space-y-8 animate-fade-in">
      
      <!-- Header -->
      <div>
        <h2 class="text-3xl font-bold text-dark-900">
          Liez vos champs CommCare aux SMS
        </h2>
        <p class="text-dark-600 mt-2">
          Définissez comment les données CommCare apparaîtront dans vos SMS
        </p>
      </div>

      <!-- Info Box -->
      <div class="bg-primary-50 border border-primary-200 rounded-lg p-4">
        <div class="flex items-start">
          <svg class="w-5 h-5 text-primary-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div class="ml-3">
            <p class="text-sm font-medium text-primary-900">Comment ça fonctionne ?</p>
            <p class="text-sm text-primary-700 mt-1">
              Les variables SMS comme <code class="px-1.5 py-0.5 bg-primary-100 rounded text-primary-800 font-mono text-xs">{case_name}</code> seront remplacées par les valeurs de vos champs CommCare.
            </p>
          </div>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="nextStep" class="space-y-6">
        
        <!-- Standard Mappings -->
        <div class="space-y-4">
          <h3 class="text-lg font-semibold text-dark-900">Champs principaux</h3>
          
          <div 
            v-for="variable in smsVariables" 
            :key="variable.key"
            class="bg-white border border-dark-200 rounded-lg p-4 hover:border-primary-300 transition-colors"
          >
            <div class="flex items-start justify-between gap-4">
              <!-- Variable Info -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <code class="px-2 py-1 bg-accent-100 text-accent-700 rounded font-mono text-sm font-semibold">
                    {{ '{' + variable.key + '}' }}
                  </code>
                  <span class="text-sm font-medium text-dark-900">{{ variable.label }}</span>
                </div>
                <p class="text-xs text-dark-600 mb-2">{{ variable.description }}</p>
                <p class="text-xs text-dark-500 font-mono">
                  Exemple : <span class="text-dark-700 font-semibold">{{ variable.example }}</span>
                </p>
              </div>

              <!-- CommCare Field Input -->
              <div class="flex-shrink-0 w-64">
                <input
                  v-model="form.mappings[variable.key]"
                  type="text"
                  required
                  class="w-full px-3 py-2 text-sm border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all font-mono"
                  placeholder="nom_champ_commcare"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Custom Mappings -->
        <div class="space-y-4 pt-6 border-t border-dark-200">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-dark-900">Champs personnalisés (optionnel)</h3>
            <button
              type="button"
              @click="addCustomMapping"
              class="px-3 py-1.5 text-sm bg-primary-50 text-primary-700 hover:bg-primary-100 rounded-lg transition-colors flex items-center space-x-1"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              <span>Ajouter un champ</span>
            </button>
          </div>

          <div 
            v-for="(mapping, index) in form.custom_mappings" 
            :key="index"
            class="flex items-center gap-3 bg-dark-50 p-4 rounded-lg"
          >
            <div class="flex-1 grid grid-cols-2 gap-3">
              <!-- SMS Variable -->
              <div>
                <label class="block text-xs font-medium text-dark-700 mb-1">Variable SMS</label>
                <input
                  v-model="mapping.sms_variable"
                  type="text"
                  class="w-full px-3 py-2 text-sm border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all font-mono"
                  placeholder="custom_field"
                />
              </div>

              <!-- CommCare Field -->
              <div>
                <label class="block text-xs font-medium text-dark-700 mb-1">Champ CommCare</label>
                <input
                  v-model="mapping.commcare_field"
                  type="text"
                  class="w-full px-3 py-2 text-sm border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all font-mono"
                  placeholder="commcare_field_name"
                />
              </div>
            </div>

            <!-- Remove Button -->
            <button
              type="button"
              @click="removeCustomMapping(index)"
              class="flex-shrink-0 w-8 h-8 flex items-center justify-center text-red-500 hover:bg-red-50 rounded-lg transition-colors mt-6"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>

        <!-- SMS Preview -->
        <div class="bg-gradient-to-br from-success-50 to-accent-50 border border-success-200 rounded-lg p-6">
          <h4 class="text-sm font-semibold text-dark-900 mb-3 flex items-center">
            <svg class="w-5 h-5 text-success-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Aperçu du SMS
          </h4>
          <div class="bg-white rounded-lg p-4 shadow-sm">
            <p class="text-sm text-dark-700 leading-relaxed">
              Bonjour <span class="font-semibold text-primary-700">{case_name}</span>, 
              votre rendez-vous <span class="font-semibold text-accent-700">{visit_number}</span> 
              est prévu le <span class="font-semibold text-success-700">{next_visit_date}</span> 
              à <span class="font-semibold text-secondary-700">{facility_name}</span>.
            </p>
          </div>
          <p class="text-xs text-dark-600 mt-3">
            ℹ️ Ceci est un exemple. Vous pourrez personnaliser vos templates SMS plus tard.
          </p>
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
            :disabled="form.processing"
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
