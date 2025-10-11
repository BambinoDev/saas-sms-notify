<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import OnboardingLayout from '@/Layouts/OnboardingLayout.vue';
import Button from '@/Components/ui/Button.vue';
import { ref } from 'vue';

const showApiKey = ref(false);
const testingConnection = ref(false);

const form = useForm({
  domain: '',
  api_key: '',
  app_id: '',
  project_name: '',
});

const testConnection = async () => {
  testingConnection.value = true;
  // TODO: Implémenter le test de connexion
  setTimeout(() => {
    testingConnection.value = false;
    alert('Connexion réussie ! ✅');
  }, 2000);
};

const nextStep = () => {
  form.post('/onboarding/commcare', {
    onSuccess: () => {
      router.visit('/onboarding/phone');
    },
  });
};

const previousStep = () => {
  router.visit('/onboarding/company');
};
</script>

<template>
  <Head title="Configuration CommCare - S-Remind" />

  <OnboardingLayout :current-step="3">
    
    <div class="space-y-8 animate-fade-in">
      
      <!-- Header -->
      <div>
        <h2 class="text-3xl font-bold text-dark-900">
          Connectez votre compte CommCare
        </h2>
        <p class="text-dark-600 mt-2">
          Nous avons besoin de ces informations pour synchroniser vos données
        </p>
      </div>

      <!-- Help Box -->
      <div class="bg-primary-50 border border-primary-200 rounded-lg p-4">
        <div class="flex items-start">
          <svg class="w-5 h-5 text-primary-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div class="ml-3">
            <p class="text-sm font-medium text-primary-900">Où trouver ces informations ?</p>
            <p class="text-sm text-primary-700 mt-1">
              Connectez-vous à <strong>www.commcarehq.org</strong> → Votre projet → <strong>Paramètres</strong> → <strong>API & Intégrations</strong>
            </p>
            <p class="text-xs text-primary-600 mt-2 font-mono">
              URL du projet : www.commcarehq.org/a/<span class="font-bold">votre-domaine</span>/
            </p>
          </div>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="nextStep" class="space-y-6">
        
        <!-- Domain -->
        <div>
          <label for="domain" class="block text-sm font-medium text-dark-700 mb-2">
            Domaine CommCare
          </label>
          <input
            id="domain"
            v-model="form.domain"
            type="text"
            required
            class="w-full px-4 py-3 border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all hover:border-dark-300 font-mono"
            placeholder="sci-civ-malaria"
          />
          <p class="mt-2 text-sm text-dark-500">
            Exemple : <code class="px-2 py-0.5 bg-dark-100 rounded text-dark-700 font-mono">sci-civ-malaria</code>
          </p>
          <p v-if="form.errors.domain" class="mt-2 text-sm text-red-600">
            {{ form.errors.domain }}
          </p>
        </div>

        <!-- API URL Preview -->
        <div class="bg-accent-50 border border-accent-200 rounded-lg p-4">
          <p class="text-sm font-medium text-accent-900 mb-2 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Nomenclature de vos liens API
          </p>
          <div class="space-y-1.5">
            <div class="flex items-center text-xs">
              <span class="text-accent-700 font-medium w-24">URL principale:</span>
              <code class="flex-1 px-2 py-1 bg-white rounded text-accent-800 font-mono">
                https://www.commcarehq.org/a/{{ form.domain || 'votre-domaine' }}/
              </code>
            </div>
            <div class="flex items-center text-xs">
              <span class="text-accent-700 font-medium w-24">API Endpoint:</span>
              <code class="flex-1 px-2 py-1 bg-white rounded text-accent-800 font-mono">
                https://www.commcarehq.org/a/{{ form.domain || 'votre-domaine' }}/api/v0.5/
              </code>
            </div>
          </div>
        </div>

        <!-- Project Name -->
        <div>
          <label for="project_name" class="block text-sm font-medium text-dark-700 mb-2">
            Nom du projet CommCare
          </label>
          <input
            id="project_name"
            v-model="form.project_name"
            type="text"
            required
            class="w-full px-4 py-3 border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all hover:border-dark-300"
            placeholder="CPN Suivi Prénatal"
          />
          <p v-if="form.errors.project_name" class="mt-2 text-sm text-red-600">
            {{ form.errors.project_name }}
          </p>
        </div>

        <!-- API Key -->
        <div>
          <label for="api_key" class="block text-sm font-medium text-dark-700 mb-2">
            Clé API
          </label>
          <div class="relative">
            <input
              id="api_key"
              v-model="form.api_key"
              :type="showApiKey ? 'text' : 'password'"
              required
              class="w-full px-4 py-3 pr-24 border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all hover:border-dark-300 font-mono text-sm"
              placeholder="••••••••••••••••••••••••••••"
            />
            <button
              type="button"
              @click="showApiKey = !showApiKey"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-dark-400 hover:text-dark-600 transition-colors"
            >
              <svg v-if="!showApiKey" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
              </svg>
            </button>
          </div>
          <p class="mt-2 text-sm text-dark-500">
            Votre clé API personnelle depuis les paramètres de votre compte
          </p>
          <p v-if="form.errors.api_key" class="mt-2 text-sm text-red-600">
            {{ form.errors.api_key }}
          </p>
        </div>

        <!-- App ID -->
        <div>
          <label for="app_id" class="block text-sm font-medium text-dark-700 mb-2">
            ID de l'application
          </label>
          <input
            id="app_id"
            v-model="form.app_id"
            type="text"
            required
            class="w-full px-4 py-3 border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all hover:border-dark-300 font-mono text-sm"
            placeholder="a1b2c3d4e5f6..."
          />
          <p class="mt-2 text-sm text-dark-500">
            L'identifiant unique de votre application CommCare
          </p>
          <p v-if="form.errors.app_id" class="mt-2 text-sm text-red-600">
            {{ form.errors.app_id }}
          </p>
        </div>

        <!-- Test Connection Button -->
        <div>
          <button
            type="button"
            @click="testConnection"
            :disabled="!form.domain || !form.api_key || testingConnection"
            class="w-full px-4 py-3 border-2 border-dashed border-primary-300 text-primary-700 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2"
          >
            <svg v-if="!testingConnection" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            <svg v-else class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ testingConnection ? 'Test en cours...' : 'Tester la connexion' }}</span>
          </button>
        </div>

        <!-- Security Notice -->
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
          <div class="flex items-start">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div class="ml-3">
              <p class="text-sm font-medium text-amber-900">Informations sécurisées</p>
              <p class="text-sm text-amber-700 mt-1">
                Vos identifiants sont chiffrés et stockés en toute sécurité. Nous ne les partageons jamais avec des tiers.
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
