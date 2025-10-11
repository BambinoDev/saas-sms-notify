<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import OnboardingLayout from '@/Layouts/OnboardingLayout.vue';
import Button from '@/Components/ui/Button.vue';

const form = useForm({
  industry: 'health',
  timezone: 'Africa/Abidjan',
  organization_type: 'hospital',
  team_size: '10-50',
});

const nextStep = () => {
  form.post('/onboarding/company', {
    onSuccess: () => {
      router.visit('/onboarding/commcare');
    },
  });
};

const previousStep = () => {
  router.visit('/onboarding/welcome');
};
</script>

<template>
  <Head title="Informations Entreprise - S-Remind" />

  <OnboardingLayout :current-step="2">
    
    <div class="space-y-8 animate-fade-in">
      
      <!-- Header -->
      <div>
        <h2 class="text-3xl font-bold text-dark-900">
          Parlez-nous de votre organisation
        </h2>
        <p class="text-dark-600 mt-2">
          Ces informations nous permettront de mieux configurer votre compte
        </p>
      </div>

      <!-- Company Name Display -->
      <div class="bg-primary-50 border border-primary-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
          <div class="w-12 h-12 bg-primary-500 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm text-primary-700 font-medium">Votre organisation</p>
            <p class="text-lg font-bold text-dark-900">{{ $page.props.auth?.user?.company_name || 'Centre Hospitalier Cocody' }}</p>
          </div>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="nextStep" class="space-y-6">
        
        <!-- Organization Type (EN PREMIER) -->
        <div>
          <label for="organization_type" class="block text-sm font-medium text-dark-700 mb-2">
            Type d'organisation
          </label>
          <select
            id="organization_type"
            v-model="form.organization_type"
            required
            class="w-full px-4 py-3 border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all hover:border-dark-300"
          >
            <option value="hospital">Hôpital / Clinique</option>
            <option value="health_center">Centre de santé</option>
            <option value="school">École / Université</option>
            <option value="cooperative">Coopérative</option>
            <option value="ngo">ONG internationale</option>
            <option value="local_ngo">ONG locale</option>
            <option value="ministry">Ministère</option>
            <option value="other">Autre</option>
          </select>
          <p v-if="form.errors.organization_type" class="mt-2 text-sm text-red-600">
            {{ form.errors.organization_type }}
          </p>
        </div>

        <!-- Industry (EN SECOND) -->
        <div>
          <label for="industry" class="block text-sm font-medium text-dark-700 mb-2">
            Secteur d'activité
          </label>
          <select
            id="industry"
            v-model="form.industry"
            required
            class="w-full px-4 py-3 border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all hover:border-dark-300"
          >
            <option value="health">🏥 Santé</option>
            <option value="education">🎓 Éducation</option>
            <option value="agriculture">🌾 Agriculture</option>
            <option value="ngo">🤝 ONG / Association</option>
            <option value="government">🏛️ Gouvernement</option>
            <option value="other">📊 Autre</option>
          </select>
          <p v-if="form.errors.industry" class="mt-2 text-sm text-red-600">
            {{ form.errors.industry }}
          </p>
        </div>

        <!-- Timezone -->
        <div>
          <label for="timezone" class="block text-sm font-medium text-dark-700 mb-2">
            Fuseau horaire
          </label>
          <select
            id="timezone"
            v-model="form.timezone"
            required
            class="w-full px-4 py-3 border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all hover:border-dark-300"
          >
            <option value="Africa/Abidjan">🇨🇮 Africa/Abidjan (GMT+0)</option>
            <option value="Africa/Accra">🇬🇭 Africa/Accra (GMT+0)</option>
            <option value="Africa/Lagos">🇳🇬 Africa/Lagos (GMT+1)</option>
            <option value="Africa/Dakar">🇸🇳 Africa/Dakar (GMT+0)</option>
            <option value="Africa/Bamako">🇲🇱 Africa/Bamako (GMT+0)</option>
            <option value="Africa/Ouagadougou">🇧🇫 Africa/Ouagadougou (GMT+0)</option>
            <option value="Africa/Douala">🇨🇲 Africa/Douala (GMT+1)</option>
            <option value="Africa/Lome">🇹🇬 Africa/Lome (GMT+0)</option>
            <option value="Africa/Porto-Novo">🇧🇯 Africa/Porto-Novo (GMT+1)</option>
          </select>
          <p class="mt-2 text-sm text-dark-500">
            Les SMS seront envoyés selon ce fuseau horaire
          </p>
          <p v-if="form.errors.timezone" class="mt-2 text-sm text-red-600">
            {{ form.errors.timezone }}
          </p>
        </div>

        <!-- Team Size -->
        <div>
          <label for="team_size" class="block text-sm font-medium text-dark-700 mb-2">
            Taille de l'équipe
          </label>
          <div class="grid grid-cols-2 gap-3">
            <label 
              v-for="size in ['1-10', '10-50', '50-200', '200+']" 
              :key="size"
              class="relative"
            >
              <input
                type="radio"
                :value="size"
                v-model="form.team_size"
                class="peer sr-only"
              />
              <div class="px-4 py-3 border-2 border-dark-200 rounded-lg cursor-pointer transition-all hover:border-primary-300 peer-checked:border-primary-500 peer-checked:bg-primary-50 text-center">
                <span class="text-sm font-medium text-dark-700">{{ size }} personnes</span>
              </div>
            </label>
          </div>
          <p v-if="form.errors.team_size" class="mt-2 text-sm text-red-600">
            {{ form.errors.team_size }}
          </p>
        </div>

        <!-- Info Box -->
        <div class="bg-accent-50 border border-accent-200 rounded-lg p-4">
          <div class="flex">
            <svg class="w-5 h-5 text-accent-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="ml-3 text-sm text-accent-800">
              Ces informations nous aident à personnaliser votre expérience et à vous proposer des templates SMS adaptés à votre secteur.
            </p>
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
